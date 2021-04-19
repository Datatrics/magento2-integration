<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Magento\Framework\App\ResourceConnection;

/**
 * Service class for category path for products
 */
class Parents
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Category constructor.
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get array of products with parent IDs and types
     *
     * Structure of response
     *
     * @param array[] $entityIds array of product IDs
     * @return array[]
     */
    public function execute($entityIds = []): array
    {
        if (empty($entityIds)) {
            return $this->collectAllParents();
        }
        return $this->collectParents($entityIds);
    }

    /**
     * Get parent product IDs
     *
     * @return array[]
     */
    private function collectAllParents(): array
    {
        $result = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['catalog_product_relation' => $this->resource->getTableName('catalog_product_relation')]
            )->joinLeft(
                ['catalog_product_entity' => $this->resource->getTableName('catalog_product_entity')],
                'catalog_product_entity.entity_id = catalog_product_relation.parent_id',
                'type_id'
            );
        foreach ($this->resource->getConnection()->fetchAll($select) as $item) {
            $result[$item['child_id']][$item['parent_id']] = $item['type_id'];
        }
        return $result;
    }

    /**
     * Get parent products IDs
     *
     * @param array[] $entityIds array of product IDs
     * @return array[]
     */
    private function collectParents(array $entityIds): array
    {
        $all = $entityIds;
        $result = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['catalog_product_relation' => $this->resource->getTableName('catalog_product_relation')]
            )->joinLeft(
                ['catalog_product_entity' => $this->resource->getTableName('catalog_product_entity')],
                'catalog_product_entity.entity_id = catalog_product_relation.parent_id',
                'type_id'
            )->where('child_id IN (?)', $entityIds);
        $relations = $this->resource->getConnection()->fetchAll($select);
        foreach ($relations as $item) {
            $result[$item['child_id']][$item['parent_id']] = $item['type_id'];
            $all += [$item['child_id'], $item['parent_id']];
        }
        return ['all' => array_unique($all), 'relations' => $result];
    }
}