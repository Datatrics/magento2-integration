<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Service class to collect configurable keys for simple products
 */
class ConfigurableKey
{

    public const REQUIRE = [
        'entity_ids'
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
    private $linkField;

    /**
     * Price constructor.
     *
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     * @throws \Exception
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->linkField = $metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }

    /**
     * Get URL data
     *
     * Structure of response
     * [product_id][store_id] = url
     *
     * @param array[] $entityIds array with IDs or products, categories or pages
     *
     * @return array[]
     */
    public function execute(array $entityIds = []): array
    {
        $this->setData('entity_ids', $entityIds);
        return $this->collectKeys();
    }

    /**
     * @param string $type
     * @param mixed $data
     */
    public function setData($type, $data): void
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
     * Collect URL data for entities
     *
     * @return array[]
     */
    private function collectKeys(): array
    {
        $result = [];
        $condition = 'catalog_product_entity_int.attribute_id = catalog_product_super_attribute.attribute_id
and catalog_product_entity_int.' . $this->linkField . ' = catalog_product_relation.child_id';
        $select = $this->resource->getConnection()
            ->select()->from(
                ['catalog_product_relation' => $this->resource->getTableName('catalog_product_relation')]
            )->joinLeft(
                ['catalog_product_super_attribute' => $this->resource->getTableName('catalog_product_super_attribute')],
                'catalog_product_super_attribute.product_id = catalog_product_relation.parent_id',
                'attribute_id'
            )->joinLeft(
                ['catalog_product_entity_int' => $this->resource->getTableName('catalog_product_entity_int')],
                $condition,
                ['value', 'store_id']
            )->where('child_id IN (?)', $this->entityIds);
        $keysData = $this->resource->getConnection()->fetchAll($select);
        foreach ($keysData as $item) {
            if (!$item['value']) {
                continue;
            }
            if (!isset($result[$item['child_id']])) {
                $result[$item['child_id']] = [];
            }
            if (!isset($result[$item['child_id']][$item['parent_id']])) {
                $result[$item['child_id']][$item['parent_id']] = [];
            }
            if (!isset($result[$item['child_id']][$item['parent_id']][$item['store_id']])) {
                $result[$item['child_id']][$item['parent_id']][$item['store_id']] = '#';
            }
            $result[$item['child_id']][$item['parent_id']][$item['store_id']]
                .= $item['attribute_id'] . '=' . $item['value'] . '&';
        }
        return $result;
    }

    /**
     * @return string[]
     */
    public function getRequiredParameters(): array
    {
        return self::REQUIRE;
    }

    /**
     * @param string $type
     */
    public function resetData($type = 'all'): void
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
}
