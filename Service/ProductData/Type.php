<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Datatrics\Connect\Service\ProductData\AttributeCollector\Data\AttributeMapper;
use Datatrics\Connect\Service\ProductData\AttributeCollector\Data\ConfigurableKey;
use Magento\Framework\App\ResourceConnection;

/**
 * Type class
 */
class Type
{

    /**
     * @var JsonSerializer
     */
    private $json;

    /**
     * @var AttributeMapper
     */
    private $attributeMapper;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ConfigurableKey
     */
    private $configurableKey;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var int
     */
    private $storeId = 0;

    /**
     * @var array
     */
    private $simpleProductData = [];

    /**
     * @var array
     */
    private $configProductData = [];

    /**
     * Data constructor.
     * @param JsonSerializer $json
     * @param AttributeMapper $attributeMapper
     * @param ResourceConnection $resourceConnection
     * @param Data $data
     * @param ConfigurableKey $configurableKey
     */
    public function __construct(
        JsonSerializer $json,
        AttributeMapper $attributeMapper,
        ResourceConnection $resourceConnection,
        Data $data,
        ConfigurableKey $configurableKey
    ) {
        $this->json = $json;
        $this->attributeMapper = $attributeMapper;
        $this->resourceConnection = $resourceConnection;
        $this->data = $data;
        $this->configurableKey = $configurableKey;
    }

    public function execute(
        $entityIds,
        $attributeMap,
        $productsBehaviour,
        $extraParameters,
        $storeId = 0,
        $limit = 10000,
        $page = 1
    ) {
        $entityIds = array_chunk($entityIds, (int)$limit);
        if (isset($entityIds[$page - 1])) {
            $entityIds = $entityIds[$page - 1];
        } else {
            $entityIds = $entityIds[0];
        }
        $this->storeId = $storeId;
        $parents = $this->collectParents();
        $toUnset = [];
        $parentAttributeToUse = [];
        $extraProductsToLoad = [];
        $parentAttributes = [
            'configurable' => $productsBehaviour['configurable']['use_parent_attributes'],
            'grouped' => $productsBehaviour['grouped']['use_parent_attributes'],
            'bundle' => $productsBehaviour['bundle']['use_parent_attributes']
        ];
        if ($productsBehaviour['configurable']['use_parent_url']) {
            $parentAttributes['configurable'][] = 'url';
        }
        if ($productsBehaviour['grouped']['use_parent_url']) {
            $parentAttributes['grouped'][] = 'url';
        }
        if ($productsBehaviour['bundle']['use_parent_url']) {
            $parentAttributes['bundle'][] = 'url';
        }
        if ($productsBehaviour['configurable']['use_parent_images']) {
            $parentAttributes['configurable'][] = 'image';
        }
        if ($productsBehaviour['grouped']['use_parent_images']) {
            $parentAttributes['grouped'][] = 'image';
        }
        if ($productsBehaviour['bundle']['use_parent_images']) {
            $parentAttributes['bundle'][] = 'image';
        }
        $parentType = false;
        foreach ($entityIds as $entityId) {
            if (!array_key_exists($entityId, $parents)) {
                continue;
            }
            $keys = array_keys($parents[$entityId]);
            $parentId = reset($keys);
            $parentType = reset($parents[$entityId]);

            if (!$productsBehaviour[$parentType]['use_parent_attributes']
                && !$productsBehaviour[$parentType]['use_parent_url']
                && !$productsBehaviour[$parentType]['use_parent_images']
            ) {
                continue;
            }
            if ($productsBehaviour[$parentType]['use'] == 'simple') {
                $toUnset[] = $parentId;
            } elseif ($productsBehaviour[$parentType]['use'] == 'parent') {
                $toUnset[] = $entityId;
            }
            if (!empty($parentAttributes[$parentType])) {
                foreach ($parentAttributes[$parentType] as $parentAttribute) {
                    $parentAttributeToUse[$entityId][$parentAttribute] = $parentId;
                }
            }
            if (!in_array($parentId, $entityIds) && !in_array($parentId, $extraProductsToLoad)) {
                $extraProductsToLoad[] = $parentId;
            }
        }
        $data = $this->data->execute(
            array_merge($entityIds, $extraProductsToLoad),
            $attributeMap,
            $extraParameters,
            $storeId
        );
        $keys = $this->configurableKey->execute(array_merge($entityIds, $extraProductsToLoad));
        foreach ($data as $entityId => $productData) {
            $filtered = $this->checkExtraFilters($extraParameters['advanced']['advanced_filters'], $productData);
            if (!$filtered) {
                $toUnset[] = $entityId;
            }
            if (array_key_exists($entityId, $parents)) {
                $keys = array_keys($parents[$entityId]);
                $data[$entityId]['parent_id'] = reset($keys);
            }
            if (array_key_exists($entityId, $parentAttributeToUse)) {
                foreach ($parentAttributeToUse[$entityId] as $parentAttribute => $parentId) {
                    if (!isset($data[$parentId][$parentAttribute])) {
                        continue;
                    }
                    $data[$entityId][$parentAttribute] = $data[$parentId][$parentAttribute];
                    if ($productsBehaviour[$parentType]['use_parent_url'] == 2 && $parentAttribute == 'url') {
                        if (!array_key_exists($entityId, $keys)) {
                            continue;
                        }
                        if (array_key_exists($this->storeId, $keys[$entityId][$parentId])) {
                            $data[$entityId]['url'] .= $keys[$entityId][$parentId][$this->storeId];
                        } else {
                            $data[$entityId]['url'] .= $keys[$entityId][$parentId][0];
                        }
                    }
                }
            }
        }
        return array_diff_key($data, array_flip($toUnset));
    }

    private function checkExtraFilters($filters, $productData): bool
    {
        foreach ($filters as $filter) {
            if (!isset($productData[$filter['attribute']])) {
                return true;
            }
            if ($productData['type_id'] != $filter['product_type']) {
                return true;
            }
            switch ($filter['condition']) {
                case 'eq':
                    return $productData[$filter['attribute']] == $filter['value'];
                case 'neq':
                    return $productData[$filter['attribute']] != $filter['value'];
                case 'gt':
                    return $productData[$filter['attribute']] > $filter['value'];
                case 'gteq':
                    return $productData[$filter['attribute']] >= $filter['value'];
                case 'lt':
                    return $productData[$filter['attribute']] < $filter['value'];
                case 'lteg':
                    return $productData[$filter['attribute']] <= $filter['value'];
                case 'in':
                    return in_array($productData[$filter['attribute']], explode(',', $filter['value']));
                case 'nin':
                    return !in_array($productData[$filter['attribute']], explode(',', $filter['value']));
                case 'like':
                    return preg_match($filter['value'], $productData[$filter['attribute']]);
                case 'empty':
                    return !$productData[$filter['attribute']];
                case 'not-empty':
                    return $productData[$filter['attribute']];
            }
        }
        return true;
    }
    /**
     * Get parent products IDs
     *
     * @return array[]
     */
    private function collectParents()
    {
        $result = [];
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from(
                ['catalog_product_relation' => $this->resourceConnection->getTableName('catalog_product_relation')]
            )->joinLeft(
                ['catalog_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'catalog_product_entity.entity_id = catalog_product_relation.parent_id',
                'type_id'
            );
        foreach ($this->resourceConnection->getConnection()->fetchAll($select) as $item) {
            $result[$item['child_id']][$item['parent_id']] = $item['type_id'];
        }
        return $result;
    }
}
