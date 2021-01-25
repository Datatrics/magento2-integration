<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Filter class
 */
class Filter
{

    /**
     * @var JsonSerializer
     */
    private $json;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    private $storeId;

    /**
     * @var string
     */
    private $entityId;
    /**
     * Data constructor.
     * @param JsonSerializer $json
     * @param ResourceConnection $resourceConnection
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        JsonSerializer $json,
        ResourceConnection $resourceConnection,
        ProductMetadataInterface $productMetadata
    ) {
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
        $this->entityId = ($productMetadata->getEdition() !== ProductMetadata::EDITION_NAME) ? 'row_id' : 'entity_id';
    }

    public function execute($filter, $storeId = 0)
    {
        $this->storeId = $storeId;
        if ($filter['filter_by_visibility']) {
            $entityIds = $this->filterVisibility(explode(',', $filter['visibility']));
        } else {
            $entityIds = $this->filterVisibility([1,2,3,4]);
        }
        if (!$filter['add_disabled_products']) {
            $entityIds = $this->filterEnabledStatus($entityIds);
        }
        if ($filter['restrict_by_category']) {
            $entityIds = $this->filterByCategories(
                $entityIds,
                $filter['category_restriction_behaviour'],
                explode(',', $filter['category'])
            );
        }
        return $entityIds;
    }

    private function filterVisibility($visibility)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->distinct()->from(
            ['catalog_product_entity_int' => $connection->getTableName('catalog_product_entity_int')],
            [$this->entityId]
        )->joinLeft(
            ['eav_attribute' => $connection->getTableName('eav_attribute')],
            'eav_attribute.attribute_id = catalog_product_entity_int.attribute_id',
            []
        )->where('value IN (?)', $visibility)
            ->where('attribute_code = ?', 'visibility')
            ->where('store_id IN (?)', [0]);
        return $connection->fetchCol($select, 'catalog_product_entity_int.' . $this->entityId);
    }

    private function filterEnabledStatus($entityIds)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->distinct()->from(
            ['catalog_product_entity_int' => $connection->getTableName('catalog_product_entity_int')],
            [$this->entityId, 'value']
        )->joinLeft(
            ['eav_attribute' => $connection->getTableName('eav_attribute')],
            'eav_attribute.attribute_id = catalog_product_entity_int.attribute_id',
            []
        )->where('value = ?', 1)
            ->where('attribute_code = ?', 'status')
            ->where('store_id IN (?)', [0])
            ->where($this->entityId . ' IN (?)', $entityIds);
        return $connection->fetchCol($select, 'catalog_product_entity_int.' . $this->entityId);
    }

    private function filterByCategories(
        $entityIds,
        $behaviour,
        $categoryIds
    ) {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->distinct()->from(
            ['catalog_category_product' => $connection->getTableName('catalog_category_product')],
            'product_id'
        )->where('product_id in (?)', $entityIds);
        if ($behaviour == 'in') {
            $select->where('category_id in (?)', $categoryIds);
        } else {
            $select->where('category_id not in (?)', $categoryIds);
        }
        return $connection->fetchCol($select, 'product_id');
    }
}
