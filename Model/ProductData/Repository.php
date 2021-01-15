<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\ProductData;

use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Api\ProductData\RepositoryInterface as ProductData;
use Datatrics\Connect\Service\ProductData\Filter;
use Datatrics\Connect\Service\ProductData\Type;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * ProductData repository class
 *
 * Prepare products data for feed based on config
 */
class Repository implements ProductData
{

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var JsonSerializer
     */
    private $json;

    /**
     * @var array
     */
    private $entityIds;

    private $type;

    /**
     * Base attributes map to pull from product
     *
     * @var array
     */
    private $attributeMap = [
        'description' => 'description',
        'short_description' => 'short_description',
        'image' => 'image',
        'type_id'  => 'type_id',
        'created_at' =>'created_at',
        'updated_at' =>'updated_at',
        'visibility' => 'visibility',
        'url' => 'url'
    ];

    /**
     * Base map of feed structure data. Values as magento data, keys as data for feed
     *
     * @var array
     */
    private $resultMap = [
        'name',
        'parent_id',
        'short_description',
        'description',
        'url',
        'sku',
        'price',
        'special_price' => 'sales_price',
        'min_price',
        'max_price',
        'updated' => 'updated_at',
        'created' => 'created_at',
        'type' => 'type_id',
        'image',
        'categories' => 'category',
        'stock' => 'qty',
        'min_sale_qty',
        'qty_increments',
        'manage_stock',
        'in_stock' => 'is_in_stock'
    ];

    /**
     * Repository constructor.
     * @param ConfigRepository $configRepository
     * @param JsonSerializer $json
     * @param Filter $filter
     * @param Type $type
     */
    public function __construct(
        ConfigRepository $configRepository,
        JsonSerializer $json,
        Filter $filter,
        Type $type
    ) {
        $this->configRepository = $configRepository;
        $this->json = $json;
        $this->type = $type;
        $this->entityIds = $filter->execute(
            $this->configRepository->getFilters()
        );
    }

    /**
     * @inheritDoc
     */
    public function getProductData($storeId = 0, array $entityIds = []): array
    {
        $this->collectAttributes();
        $advancedFilters = $this->configRepository->getFiltersData((int)$storeId);
        foreach ($advancedFilters as $filter) {
            $this->attributeMap[] = $filter['attribute'];
        }
        $productsBehaviour = [
            'configurable' => $this->configRepository->getConfigProductsBehaviour(),
            'bundle' => $this->configRepository->getBundleProductsBehaviour(),
            'grouped' => $this->configRepository->getGroupedProductsBehaviour()
        ];
        $extraParameters = [
            'advanced' => [
                'inventory' => $this->configRepository->getInventory(),
                'inventory_fields' => $this->configRepository->getInventoryFields(),
                'advanced_filters' => $advancedFilters
            ]
        ];
        $data = $this->type->execute(
            $this->entityIds,
            $this->attributeMap,
            $productsBehaviour,
            $extraParameters,
            $storeId
        );
        $result = [];
        foreach ($data as $entityId => $datum) {
            foreach ($this->resultMap as $index => $attr) {
                if (!is_int($index)) {
                    if (array_key_exists($attr, $datum)) {
                        $result[$entityId][$index] = $datum[$attr];
                    } else {
                        $result[$entityId][$index] = '';
                    }
                } else {
                    if (array_key_exists($attr, $datum)) {
                        $result[$entityId][$attr] = $datum[$attr];
                    } else {
                        $result[$entityId][$attr] = '';
                    }
                }
            }
        }

        return $result;
    }

    /**
     *
     */
    private function collectAttributes()
    {
        $this->attributeMap += [
            'name' => $this->configRepository->getName(),
            'sku' => $this->configRepository->getSku()
        ];
        if ($this->configRepository->getImage() == 'all') {
            $this->attributeMap += [
                'image' => 'image',
                'small_image' => 'small_image',
                'thumbnail' => 'thumbnail',
                'swatch_image' => 'swatch_image'
            ];
        } else {
            $this->attributeMap['image'] = $this->configRepository->getImage();
        }
        $extraFields = $this->json->unserialize(
            $this->configRepository->getExtraFields()
        );
        foreach ($extraFields as $field) {
            $this->attributeMap[$field['name']] = $field['attribute'];
            $this->resultMap[$field['name']] = $field['attribute'];
        }
        $this->attributeMap = array_filter($this->attributeMap);
    }
}
