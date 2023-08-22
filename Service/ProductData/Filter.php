<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Filter class
 */
class Filter
{

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var string
     */
    private $entityId;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Data constructor.
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @throws Exception
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->entityId = $metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }

    /**
     * Execute filters and return product entity ids
     *
     * @param array $filter
     * @param int $storeId
     * @return array
     */
    public function execute(array $filter, int $storeId = 0): array
    {
        $entityIds = $this->filterVisibility($filter);

        if ($storeId) {
            $websiteId = $this->getWebsiteId($storeId);
            $entityIds = $this->filterWebsite($entityIds, $websiteId);
        }

        if (!$filter['add_disabled_products']) {
            $entityIds = $this->filterEnabledStatus($entityIds);
        }

        if ($filter['restrict_by_category']) {
            $entityIds = $this->filterByCategories(
                $entityIds,
                $filter['category_restriction_behaviour'],
                $filter['category']
            );
        }

        return $entityIds;
    }

    /**
     * Filter entity ids to exclude products based on visibility
     *
     * @param array $filter
     * @return array
     */
    private function filterVisibility(array $filter): array
    {
        if ($filter['filter_by_visibility']) {
            $visibility = is_array($filter['visibility'])
                ? $filter['visibility']
                : explode(',', $filter['visibility']);
        } else {
            $visibility = [
                Visibility::VISIBILITY_NOT_VISIBLE,
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_IN_SEARCH,
                Visibility::VISIBILITY_BOTH,
            ];
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->distinct()->from(
            ['catalog_product_entity_int' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
            [$this->entityId]
        )->joinLeft(
            ['eav_attribute' => $this->resourceConnection->getTableName('eav_attribute')],
            'eav_attribute.attribute_id = catalog_product_entity_int.attribute_id',
            []
        )->where(
            'value IN (?)',
            $visibility
        )->where(
            'attribute_code = ?',
            'visibility'
        )->where(
            'store_id IN (?)',
            [0]
        );

        return $connection->fetchCol($select);
    }

    /**
     * @param int $storeId
     * @return int
     */
    private function getWebsiteId(int $storeId = 0): int
    {
        try {
            return (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * Filter entity ids to exclude products by website
     *
     * @param array $entityIds
     * @param int $websiteId
     * @return array
     */
    private function filterWebsite(array $entityIds, int $websiteId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['catalog_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            [$this->entityId]
        )->join(
            ['catalog_product_website' => $this->resourceConnection->getTableName('catalog_product_website')],
            'catalog_product_entity.entity_id = catalog_product_website.product_id',
        )->where(
            'catalog_product_website.website_id = ?',
            $websiteId
        )->where(
            "catalog_product_entity.{$this->entityId} in (?)",
            $entityIds
        );

        return $connection->fetchCol($select);
    }

    /**
     * Filter entity ids to exclude products with status disabled
     *
     * @param array $entityIds
     * @return array
     */
    private function filterEnabledStatus(array $entityIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->distinct()->from(
            ['catalog_product_entity_int' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
            [$this->entityId]
        )->join(
            ['eav_attribute' => $this->resourceConnection->getTableName('eav_attribute')],
            'eav_attribute.attribute_id = catalog_product_entity_int.attribute_id',
            []
        )->where(
            'value = ?',
            Status::STATUS_ENABLED
        )->where(
            'attribute_code = ?',
            'status'
        )->where(
            'store_id IN (?)',
            [0]
        )->where(
            $this->entityId . ' IN (?)',
            $entityIds
        );

        return $connection->fetchCol($select);
    }

    /**
     * Filter entity ids to exclude products based on category ids
     *
     * @param array $entityIds
     * @param string $behaviour
     * @param array $categoryIds
     * @return array
     */
    private function filterByCategories(array $entityIds, string $behaviour, array $categoryIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['catalog_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            [$this->entityId]
        )->join(
            ['catalog_category_product' => $this->resourceConnection->getTableName('catalog_category_product')],
            'catalog_product_entity.entity_id = catalog_category_product.product_id',
        )->where(
            "catalog_product_entity.{$this->entityId} in (?)",
            $entityIds
        )->group($this->entityId);

        if ($behaviour == 'in') {
            $select->where('catalog_category_product.category_id in (?)', $categoryIds);
        } else {
            $select->where('catalog_category_product.category_id not in (?)', $categoryIds);
        }

        return $connection->fetchCol($select);
    }
}
