<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Product\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Service class for stock data
 */
class Stock
{

    public const REQIURE = [
        'entity_ids'
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var array[]
     */
    private $entityIds;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * Price constructor.
     *
     * @param ResourceConnection $resource
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ResourceConnection $resource,
        ModuleManager $moduleManager
    ) {
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get stock data
     *
     * @param array[] $entityIds
     * @return array[]
     */
    public function execute(array $entityIds = []): array
    {
        $this->setData('entity_ids', $entityIds);
        return ($this->isMsiEnabled())
            ? $this->getMsiStock()
            : $this->getNoMsiStock();
    }

    /**
     * Get stock qty for products without MSI
     *
     * Structure of response
     * [product_id] => [
     *      qty
     *      is_in_stock
     *      reserved
     *      salable_qty
     *      manage_stock
     *      qty_increments
     *      min_sale_qty
     * ]
     *
     * @return array[]
     */
    private function getNoMsiStock(): array
    {
        $result = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName('cataloginventory_stock_item'),
                [
                    'product_id',
                    'qty',
                    'is_in_stock',
                    'manage_stock',
                    'qty_increments',
                    'min_sale_qty'
                ]
            )->joinLeft(
                ['catalog_product_entity' => $this->resource->getTableName('catalog_product_entity')],
                'catalog_product_entity.entity_id = cataloginventory_stock_item.product_id',
                ['sku']
            );
        if ($this->resource->getConnection()->isTableExists(
            $this->resource->getTableName('inventory_reservation')
        )) {
            $select->joinLeft(
                ['inventory_reservation' => $this->resource->getTableName('inventory_reservation')],
                'inventory_reservation.sku = catalog_product_entity.sku',
                ['reserved' => 'COALESCE(inventory_reservation.quantity, 0)']
            );
        }
        $select->where('cataloginventory_stock_item.product_id IN (?)', $this->entityIds);
        $values = $this->resource->getConnection()->fetchAll($select);

        foreach ($values as $value) {
            $value['reserved'] = ($value['reserved']) ?? 0;
            $result[$value['product_id']] =
                [
                    'qty' => $value['qty'],
                    'is_in_stock' => $value['is_in_stock'],
                    'reserved' => $value['reserved'],
                    'salable_qty' => max($value['qty'], ($value['qty'] - ($value['reserved']) * -1 )),
                    'manage_stock' => $value['manage_stock'],
                    'qty_increments' => $value['qty_increments'],
                    'min_sale_qty' => $value['min_sale_qty']
                ];
        }
        return $result;
    }

    public function getRequiredParameters()
    {
        return self::REQIURE;
    }

    public function resetData($type = 'all')
    {
        if ($type == 'all') {
            unset($this->entityIds);
        }
        switch ($type) {
            case 'entity_ids':
                unset($this->entityIds);
                break;
        }
    }

    public function setData($type, $data)
    {
        if (!$data) {
            return;
        }
        switch ($type) {
            case 'entity_ids':
                $this->entityIds = $data;
                break;
        }
    }

    /**
     * Get MSI stock channels
     *
     * @return array[]
     */
    private function getChannels(): array
    {
        $selectChannels = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName('inventory_stock_sales_channel'),
                [
                    'stock_id'
                ]
            )->where('type = ?', 'website');
        $channels = array_unique($this->resource->getConnection()->fetchCol($selectChannels));
        if (count($channels) == 1 && reset($channels) != 1) {
            $channels = [1];
        }
        return $channels;
    }

    /**
     * Collect MSI stock data
     *
     * @param array[] $channels
     * @return array[]
     */
    private function collectMsi(array $channels): array
    {
        $channel = array_pop($channels);
        $stockTablePrimary = sprintf('inventory_stock_%s', $channel);
        $selectStock = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName($stockTablePrimary),
                [
                    'product_id',
                    'website_id',
                    sprintf('quantity_%s', $channel) => 'quantity'
                ]
            );
        foreach ($channels as $channel) {
            $stockTable = sprintf('inventory_stock_%s', $channel);
            $selectStock->joinLeft(
                $stockTable,
                "${stockTable}.website_id = ${stockTablePrimary}.website_id and
                 ${stockTable}.product_id = ${stockTablePrimary}.product_id",
                [
                    sprintf('quantity_%s', $channel) => 'quantity'
                ]
            );
        }
        $selectStock->where("${stockTablePrimary}.product_id IN (?)", $this->entityIds);
        return $this->resource->getConnection()->fetchAll($selectStock);
    }

    /**
     * Get stock qty for specified products if MSI enabled
     *
     * Structure of response
     * [product_id] => [
     *      qty
     *      is_in_stock
     *      reserved
     *      salable_qty
     *      ["msi"]=> [
     *          website_id => [
     *              qty
     *              salable_qty
     *          ]
     *      ]
     * ]
     *
     * @return array[]
     */
    private function getMsiStock(): array
    {
        $channels = $this->getChannels();
        $stockData = $this->collectMsi($channels);
        $result = $this->getNoMsiStock();
        foreach ($stockData as $value) {
            foreach ($channels as $channel) {
                if (!array_key_exists($value['product_id'], $result)) {
                    continue;
                }
                $qty = $result[$value['product_id']]['qty'];
                $reserved = $result[$value['product_id']]['reserved'] * -1;
                $salableQty = max($qty, $qty - $reserved);
                $result[$value['product_id']]
                ['msi']
                [$value['website_id']] = [
                    'qty' => $value[sprintf('quantity_%s', $channel)],
                    'salable_qty' => $salableQty
                ];
            }
        }
        return $result;
    }

    /**
     * Check is MSI enabled
     *
     * @return bool
     */
    private function isMsiEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }
}
