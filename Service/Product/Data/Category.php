<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Product\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Service class for category path for products
 */
class Category
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
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * Category constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ResourceConnection $resource,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->resource = $resource;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get array of products with path of all assigned categories
     *
     * Structure of response
     * [product_id] = [path1, path2, ..., pathN]
     *
     * @param array[] $entityIds array of product IDs
     * @return array[]
     */
    public function execute($entityIds = []): array
    {
        $this->setData('entity_ids', $entityIds);
        return $this->collectCategories();
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
     * Get path data assigned to products
     *
     * @return array[]
     */
    private function collectCategories()
    {
        $path = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['catalog_category_product' => $this->resource->getTableName('catalog_category_product')],
                'product_id'
            )->joinLeft(
                ['catalog_category_entity' => $this->resource->getTableName('catalog_category_entity')],
                'catalog_category_entity.entity_id = catalog_category_product.category_id',
                'path'
            )->where('product_id IN (?)', $this->entityIds);
        $result = $this->resource->getConnection()->fetchAll($select);
        foreach ($result as $item) {
            $path[$item['product_id']][] = $item['path'];
        }
        return $path;
    }
}
