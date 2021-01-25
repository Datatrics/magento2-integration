<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Service class for attribute data
 */
class AttributeMapper
{
    /**
     * Values table pattern
     */
    const TABLE_PATTERN = '%s_%s';

    /**
     * Data set to fetch from eav_attribute table
     */
    const EAV_ATTRIBUTES_DATA_SET = [
        'attribute_id',
        'attribute_code',
        'backend_type',
        'frontend_input'
    ];

    /**
     * Data set to fetch from eav_entity_type table
     */
    const EAV_ENTITY_TYPE_DATA_SET = [
        'entity_type_code',
        'entity_type_id',
        'entity_table'
    ];

    /**
     *
     */
    const REQIURE = [
        'entity_ids',
        'store_id',
        'map',
        'entity_type_code'
    ];

    /**
     * Array of attributes to fetch
     *
     * @var array[]
     */
    private $map = [
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Type code for entities like products, customers, etc.
     * Allowed parameters is eav_entity_type from eav_entity_type table
     *
     * @var string
     */
    private $entityTypeCode;

    /**
     * Type of attributes, like int, varchar, decimal, etc.
     *
     * @var string
     */
    private $entityTypeId;

    /**
     * Base Entity table where attribute value will be looking for
     *
     * @var string
     */
    private $entityTable;

    /**
     * Store ID
     *
     * @var string|array
     */
    private $storeId;

    /**
     * ID's connected to attribute values
     *
     * @var array[]
     */
    private $entityIds;

    /**
     * Array of attribute values grouped by store and entity ID's
     *
     * @var array[]
     */
    private $result = [];

    /**
     * @var array[]
     */
    private $attrOptions = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var bool
     */
    private $isCommerce;

    /**
     * Price constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->isCommerce = ($productMetadata->getEdition() !== ProductMetadata::EDITION_NAME);
    }

    /**
     * Get attribute value for specified set
     *
     * @param array[] $entityIds ID's of entities which attribute values should be fetched
     * @param array[] $map array of attribute codes
     * @param string $entityTypeCode allowed parameters is entity_type_code from eav_entity_type table
     * @param string $storeId fetch attributes for specific store
     *
     * @return array[] has form like [%attribute_code%][%entity_id%][%store_id%]
     */
    public function execute(
        array $entityIds = [],
        array $map = [],
        string $entityTypeCode = '',
        string $storeId = 'all'
    ): array {
        $this->attrOptions = $this->collectAttributeOptions();
        $this->setData('map', $map);
        $this->setData('entity_ids', $entityIds);
        $this->setData('entity_type_code', $entityTypeCode);
        $this->setData('store_id', $storeId);
        $attributes = $this->getAttributes();
        $this->getAttributeValue($attributes);
        $this->collectExtraData();
        return $this->result;
    }

    /**
     * Collect data not related to attributes
     */
    private function collectExtraData()
    {
        $fields = ['entity_id', 'type_id', 'created_at', 'updated_at'];
        $select = $this->resource->getConnection()->select()
            ->from(
                ['catalog_product_entity' => $this->resource->getTableName('catalog_product_entity')],
                $fields
            )->where('entity_id IN (?)', $this->entityIds);
        $items = $this->resource->getConnection()->fetchAll($select);
        foreach ($items as $item) {
            foreach ($fields as $field) {
                $this->result[$field][$item['entity_id']] = $item[$field];
            }
        }
    }

    /**
     * @param string $type
     */
    public function resetData($type = 'all')
    {
        if ($type == 'all') {
            unset($this->entityIds);
            unset($this->map);
            unset($this->entityTypeCode);
            unset($this->storeId);
        }
        switch ($type) {
            case 'store_id':
                unset($this->storeId);
                break;
            case 'entity_ids':
                unset($this->entityIds);
                break;
            case 'map':
                unset($this->map);
                break;
            case 'entity_type_code':
                unset($this->entityTypeCode);
                break;
        }
    }

    /**
     * @param string $type
     * @param mixed $data
     */
    public function setData($type, $data)
    {
        if (!$data) {
            return;
        }
        switch ($type) {
            case 'store_id':
                $this->storeId = [0, $data];
                break;
            case 'entity_ids':
                $this->entityIds = $data;
                break;
            case 'map':
                $this->map = $data;
                break;
            case 'entity_type_code':
                $this->entityTypeCode = $data;
                break;
        }
    }

    /**
     * @return array
     */
    public function getRequiredParameters()
    {
        return self::REQIURE;
    }

    /**
     * Get specific attribute values for selected entities for all existing stores
     *
     * @param array[] $attributes 'attribute_id', 'attribute_code', 'backend_type', 'entity_table'
     *
     */
    private function getAttributeValue(array $attributes)
    {
        $tablesNonStatic = [];
        $attributeIdsNonStatic = [];
        $attributeStaticCode = ['entity_id'];
        $relations = [];
        $withUrl = [];
        foreach ($attributes as $attribute) {
            if ($attribute['frontend_input']== 'media_image' || $attribute['frontend_input']== 'image') {
                $withUrl[] = $attribute['attribute_id'];
            }
            $relations[$attribute['attribute_id']] = $attribute['attribute_code'];
            if ($attribute['backend_type'] != 'static') {
                $tablesNonStatic[] = $attribute['entity_table'] . '_' . $attribute['backend_type'];
                $attributeIdsNonStatic[] = $attribute['attribute_id'];
            } else {
                $attributeStaticCode[] = $attribute['attribute_code'];
            }
        }
        $tablesNonStatic = array_unique($tablesNonStatic);
        $entityTable = reset($attributes)['entity_table'];

        foreach ($tablesNonStatic as $table) {
            if ($this->isCommerce) {
                $fields = ['attribute_id', 'store_id', 'value', 'entity_id' => 'row_id'];
            } else {
                $fields = ['attribute_id', 'store_id', 'value', 'entity_id'];
            }
            $select = $this->resource->getConnection()->select()
                ->from(
                    [$table => $this->resource->getTableName($table)],
                    $fields
                );
            if ($this->isCommerce) {
                $select->where('row_id IN (?)', $this->entityIds);
            } else {
                $select->where('entity_id IN (?)', $this->entityIds);
            }
            $select->where('attribute_id in (?)', $attributeIdsNonStatic)
                ->where('store_id in (?)', $this->storeId);
            $result = $this->resource->getConnection()->fetchAll($select);
            foreach ($result as $item) {
                if (array_key_exists($item['attribute_id'], $this->attrOptions)) {
                    $attrValues = explode(',', $item['value']);
                    $item['value'] = [];
                    foreach ($attrValues as $attrValue) {
                        $attributeId = (string)$item['attribute_id'];
                        $item['value'][] = $this->attrOptions[$attributeId]
                        [$attrValue]
                        [$item['store_id']];
                    }
                    $item['value'] = implode(',', $item['value']);
                }
                if (isset($this->result[$relations[$item['attribute_id']]][$item['entity_id']])
                    && $item['store_id'] == 0) {
                    continue;
                }
                if (in_array($item['attribute_id'], $withUrl)) {
                    $item['value'] = $this->storeManager->getStore()
                           ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                        . 'catalog/product' . $item['value'];
                }
                $this->result[$relations[$item['attribute_id']]][$item['entity_id']] =
                    str_replace(["\r", "\n"], '', strip_tags((string)$item['value']));
            }
        }

        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName($entityTable),
                $attributeStaticCode
            );
        if ($this->isCommerce) {
            $select->where('row_id IN (?)', $this->entityIds);
        } else {
            $select->where('entity_id IN (?)', $this->entityIds);
        }
        $result = $this->resource->getConnection()->fetchAll($select);
        foreach ($result as $item) {
            foreach ($attributeStaticCode as $static) {
                if ($static == 'entity_id') {
                    continue;
                }
                $this->result[$static][$item['entity_id']] = $item[$static];
            }
        }
    }

    /**
     * Fetch attributes data from eav_attribute table according provided map
     *
     * @return array[] 'attribute_id', 'entity_type_id', 'attribute_code' and 'backend_type'
     */
    private function getAttributes(): array
    {
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName('eav_attribute'),
                self::EAV_ATTRIBUTES_DATA_SET
            )->joinLeft(
                ['eav_entity_type' => $this->resource->getTableName('eav_entity_type')],
                'eav_attribute.entity_type_id = eav_entity_type.entity_type_id',
                ['entity_table']
            )->where('eav_entity_type.entity_type_code = ?', $this->entityTypeCode)
            ->where('eav_attribute.attribute_code IN (?)', $this->map);
        return $this->resource->getConnection()->fetchAll($select);
    }

    /**
     * @return array
     */
    private function collectAttributeOptions()
    {
        $attrOptions = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName('eav_attribute_option'),
                ['attribute_id']
            )->joinLeft(
                ['eav_attribute_option_value' => $this->resource->getTableName('eav_attribute_option_value')],
                'eav_attribute_option_value.option_id = eav_attribute_option.option_id',
                [
                    'option_id',
                    'store_id',
                    'value'
                ]
            );
        $options = $this->resource->getConnection()->fetchAll($select);
        foreach ($options as $option) {
            $attrOptions[$option['attribute_id']][$option['option_id']][$option['store_id']] = $option['value'];
        }
        return $attrOptions;
    }
}
