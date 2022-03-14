<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Product\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CatalogPrice;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Service class for price data
 */
class PriceNew
{

    public const REQIURE = [
        'entity_ids',
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var array
     */
    private $entityIds;

    /**
     * @var string
     */
    private $type;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    private $scopeConfig;

    private $defaultTaxClass;

    private $taxClassId;

    /**
     * Price constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ResourceConnection $resource,
        StoreRepositoryInterface $storeRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resource = $resource;
        $this->storeRepository = $storeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->getDefaultTaxClass();
    }

    public function execute(array $entityIds = []): array
    {
        $this->setData('entity_ids', $entityIds);
        $this->taxClassId = $this->collectTaxClassIds();
        return $this->collectPrices();
    }

    private function collectPrices()
    {
        $prices = $this->getData('catalog_product_entity_decimal', 'price');
        $specialToDate = $this->getData('catalog_product_entity_datetime', 'special_to_date');
        $specialFromDate = $this->getData('catalog_product_entity_datetime', 'special_from_date');
        $specialPrices = $this->getData('catalog_product_entity_decimal', 'special_price');
        $specialPriceData = array_merge($specialPrices, $specialFromDate, $specialToDate, $prices);
        $result = [];
        foreach ($specialPriceData as $item) {
            list($value, $key) = [end($item), key($item)];
            $result[$item['entity_id']][$item['store_id']][$key] = $value;
        }

        return $result;
    }

    private function getData($table, $attr)
    {
        $selectPrice = $this->resource->getConnection()
            ->select()
            ->from(
                ['eav_attribute' => $this->resource->getTableName('eav_attribute')],
                []
            )->joinLeft(
                [$table => $this->resource->getTableName($table)],
                $table . '.attribute_id = eav_attribute.attribute_id',
                [
                    'entity_id',
                    'store_id',
                    $attr => 'value'
                ]
            )->where('eav_attribute.attribute_code = ?', $attr)
            ->where($table . '.entity_id IN (?)', $this->entityIds);
        return  $this->resource->getConnection()->fetchAll($selectPrice);
    }

    private function getDefaultTaxClass()
    {
        $this->defaultTaxClass
            = $this->scopeConfig->getValue('tax/classes/default_product_tax_class');
    }

    private function collectTaxClassIds()
    {
        $selectTaxClass = $this->resource->getConnection()
            ->select()
            ->from(
                ['eav_attribute' => $this->resource->getTableName('eav_attribute')],
                []
            )->joinLeft(
                ['catalog_product_entity_int' => $this->resource->getTableName('catalog_product_entity_int')],
                'catalog_product_entity_int.attribute_id = eav_attribute.attribute_id',
                [
                    'entity_id',
                    'value'
                ]
            )->where('eav_attribute.attribute_code = ?', 'tax_class_id')
            ->where('catalog_product_entity_int.entity_id IN (?)', $this->entityIds);
        $result = $this->resource->getConnection()->fetchPairs($selectTaxClass);
        foreach ($this->entityIds as $entityId) {
            if (!array_key_exists($entityId, $result)) {
                $result[$entityId] = $this->defaultTaxClass;
            }
        }
        return $result;
    }

    private function collectTaxData()
    {
        $selectTax = $this->resource->getConnection()
            ->select()
            ->from(
                ['tax_calculation_rate' => $this->resource->getTableName('tax_calculation_rate')],
                []
            )->joinLeft(
                ['tax_calculation' => $this->resource->getTableName('tax_calculation')],
                'tax_calculation_rate.tax_calculation_rate_id = tax_calculation.tax_calculation_rate_id',
                [
                    'tax_calculation.product_tax_class_id',
                    'tax_calculation_rate.rate'
                ]
            );
        $result = [];
        $taxes = $this->resource->getConnection()->fetchPairs($selectTax);
        foreach ($this->taxClassId as $entityId => $taxClasses) {
            $result[$entityId] = $taxes[$taxClasses];
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
            unset($this->type);
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
}
