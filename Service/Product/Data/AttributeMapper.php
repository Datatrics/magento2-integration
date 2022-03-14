<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Product\Data;

use Magento\Framework\App\ResourceConnection;

/**
 * Service class for attribute data
 */
class AttributeMapper
{
    /**
     * Values table pattern
     */
    public const TABLE_PATTERN = '%s_%s';

    /**
     * Data set to fetch from eav_attribute table
     */
    public const EAV_ATTRIBUTES_DATA_SET = [
        'attribute_id',
        'attribute_code',
        'backend_type'
    ];

    /**
     * Data set to fetch from eav_entity_type table
     */
    public const EAV_ENTITY_TYPE_DATA_SET = [
        'entity_type_code',
        'entity_type_id',
        'entity_table'
    ];

    /**
     *
     */
    public const REQIURE = [
        'entity_ids',
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
     * Price constructor.
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get attribute value for specified set
     *
     * @param array[] $entityIds ID's of entities which attribute values should be fetched
     * @param array[] $map array of attribute codes
     * @param string $entityTypeCode allowed parameters is entity_type_code from eav_entity_type table
     *
     * @return array[] has form like [%attribute_code%][%entity_id%][%store_id%]
     */
    public function execute(
        array $entityIds = [],
        array $map = [],
        string $entityTypeCode = ''
    ): array {
        $this->attrOptions = $this->collectAttributeOptions();
        $this->setData('map', $map);
        $this->setData('entity_ids', $entityIds);
        $this->setData('entity_type_code', $entityTypeCode);
        $attributes = $this->getAttributes();
        $this->getAttributeValue($attributes);
        return $this->result;
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
        }
        switch ($type) {
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
        foreach ($attributes as $attribute) {
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
            $select = $this->resource->getConnection()->select()
                ->from(
                    [$table => $this->resource->getTableName($table)],
                    ['attribute_id', 'store_id', 'value', 'entity_id']
                )->where('entity_id IN (?)', $this->entityIds)
                ->where('attribute_id in (?)', $attributeIdsNonStatic);
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
                $key = $relations[$item['attribute_id']];
                $this->result[$key][$item['entity_id']][$item['store_id']] = $item['value'];
            }
        }
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName($entityTable),
                $attributeStaticCode
            )->where('entity_id in (?)', $this->entityIds);
        $result = $this->resource->getConnection()->fetchAll($select);
        foreach ($result as $item) {
            foreach ($attributeStaticCode as $static) {
                if ($static == 'entity_id') {
                    continue;
                }
                $this->result[$static][$item['entity_id']][0] = $item[$static];
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
