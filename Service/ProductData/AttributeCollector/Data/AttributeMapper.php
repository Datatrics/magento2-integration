<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
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
     * Required attributes
     */
    const REQUIRE = [
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
    private $map = [];

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
     * Store ID
     *
     * @var int|array
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
     * @var ?string
     */
    private $mediaUrl = null;
    /**
     * @var string
     */
    private $linkField;

    /**
     * AttributeMapper constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @throws Exception
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->linkField = $metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }

    /**
     * Get attribute value for specified set
     *
     * @param array[] $entityIds ID's of entities which attribute values should be fetched
     * @param array[] $map array of attribute codes
     * @param string $entityTypeCode allowed parameters is entity_type_code from eav_entity_type table
     * @param int $storeId fetch attributes for specific store
     *
     * @return array[] has form like [%attribute_code%][%entity_id%][%store_id%]
     */
    public function execute(
        array $entityIds = [],
        array $map = [],
        string $entityTypeCode = '',
        int $storeId = 0
    ): array {
        $this->attrOptions = $this->collectAttributeOptions();
        $this->setData('map', $map);
        $this->setData('entity_ids', $entityIds);
        $this->setData('entity_type_code', $entityTypeCode);
        $this->setData('store_id', $storeId);
        $attributes = $this->getAttributes();
        $this->collectAttributeValues($attributes);
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
    public function resetData($type = 'all'): void
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
    public function setData($type, $data): void
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
    public function getRequiredParameters(): array
    {
        return self::REQUIRE;
    }

    /**
     * Get specific attribute values for selected entities for all existing stores
     *
     * @param array[] $attributes 'attribute_id', 'attribute_code', 'backend_type', 'entity_table'
     *
     */
    private function collectAttributeValues(array $attributes): void
    {
        $tablesNonStatic = [];
        $attributeIdsNonStatic = [];
        $attributeStaticCode = ['entity_id', 'type_id'];
        $relations = [];
        $withUrl = [];

        foreach ($attributes as $attribute) {
            if ($attribute['frontend_input'] == 'media_image' || $attribute['frontend_input'] == 'image') {
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
            $fields = ['attribute_id', 'store_id', 'value', 'entity_id' => $this->linkField];
            $select = $this->resource->getConnection()->select()
                ->from(
                    [$table => $this->resource->getTableName($table)],
                    $fields
                );
            $select->where("{$this->linkField} IN (?)", $this->entityIds);
            $select->where('attribute_id in (?)', $attributeIdsNonStatic);
            $select->where('store_id in (?)', $this->storeId);
            $result = $this->resource->getConnection()->fetchAll($select);
            foreach ($result as $item) {
                if (array_key_exists($item['attribute_id'], $this->attrOptions)) {
                    $attrValues = explode(',', (string)$item['value']);
                    $item['value'] = [];
                    foreach ($attrValues as $attrValue) {
                        $attributeId = (string)$item['attribute_id'];
                        try {
                            $item['value'][] = $this->attrOptions[$attributeId]
                            [$attrValue]
                            [$item['store_id']];
                        } catch (\Exception $exception) {
                            continue;
                        }
                    }
                    $item['value'] = implode(',', $item['value']);
                }
                if (isset($this->result[$relations[$item['attribute_id']]][$item['entity_id']])
                    && $item['store_id'] == 0) {
                    continue;
                }
                if (in_array($item['attribute_id'], $withUrl)) {
                    $item['value'] = $this->getMediaUrl('catalog/product' . $item['value']);
                }
                $this->result[$relations[$item['attribute_id']]][$item['entity_id']] =
                    str_replace(["\r", "\n"], '', (string)$item['value']);
            }
        }
        $this->adjustTaxClassLabels();
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName($entityTable),
                $attributeStaticCode
            );
        $select->where("{$this->linkField} IN (?)", $this->entityIds);
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

    private function adjustTaxClassLabels()
    {
        if (!array_key_exists('tax_class_id', $this->result)) {
            return;
        }
        $connection = $this->resource->getConnection();
        $selectClasses = $connection->select()->from(
            $this->resource->getTableName('tax_class'),
            ['class_id', 'class_name']
        );
        $taxClassLabels = $connection->fetchPairs($selectClasses);
        foreach ($this->result['tax_class_id'] as &$taxClassId) {
            $taxClassId = $taxClassLabels[$taxClassId];
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getMediaUrl($path): string
    {
        if ($this->mediaUrl == null) {
            try {
                $this->mediaUrl = $this->storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            } catch (\Exception $exception) {
                $this->mediaUrl = '';
            }
        }
        return $this->mediaUrl . $path;
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
                ['eav_attribute' => $this->resource->getTableName('eav_attribute')],
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
     * Attribute options collector
     *
     * @return array
     */
    private function collectAttributeOptions(): array
    {
        $attrOptions = [];

        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['eav_attribute_option' => $this->resource->getTableName('eav_attribute_option')],
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
