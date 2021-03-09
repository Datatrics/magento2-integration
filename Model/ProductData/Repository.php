<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\ProductData;

use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Datatrics\Connect\Api\ProductData\RepositoryInterface as ProductData;
use Datatrics\Connect\Service\ProductData\AttributeCollector\Data\Image;
use Datatrics\Connect\Service\ProductData\Filter;
use Datatrics\Connect\Service\ProductData\Type;

/**
 * ProductData repository class
 *
 * Prepare products data for feed based on config
 */
class Repository implements ProductData
{

    /**
     * Base attributes map to pull from product
     *
     * @var array
     */
    private $attributeMap = [
        'type_id' => 'type_id',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
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
        'additional_images',
        'categories' => 'category',
        'stock' => 'qty',
        'min_sale_qty',
        'qty_increments',
        'manage_stock',
        'in_stock' => 'is_in_stock'
    ];

    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;
    /**
     * @var array
     */
    private $entityIds;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var Image
     */
    private $image;
    /**
     * @var array
     */
    private $imageData;

    /**
     * Repository constructor.
     * @param ContentConfigRepository $contentConfigRepository
     * @param Filter $filter
     * @param Type $type
     * @param Image $image
     */
    public function __construct(
        ContentConfigRepository $contentConfigRepository,
        Filter $filter,
        Type $type,
        Image $image
    ) {
        $this->contentConfigRepository = $contentConfigRepository;
        $this->filter = $filter;
        $this->type = $type;
        $this->image = $image;
    }

    /**
     * @inheritDoc
     */
    public function getProductData(int $storeId = 0, array $entityIds = []): array
    {
        $this->collectIds($storeId, $entityIds);
        $this->collectAttributes($storeId);
        $this->imageData = $this->image->execute($entityIds, $storeId);
        $productDataRows = $this->collectProductData($storeId);

        $result = [];
        foreach ($productDataRows as $entityId => $productData) {
            $this->addImageData($storeId, $entityId, $productData);
            foreach ($this->resultMap as $index => $attr) {
                if (!is_int($index)) {
                    if (array_key_exists($attr, $productData)) {
                        $result[$entityId][$index] = $productData[$attr];
                    } else {
                        $result[$entityId][$index] = '';
                    }
                } else {
                    if (array_key_exists($attr, $productData)) {
                        $result[$entityId][$attr] = $productData[$attr];
                    } else {
                        $result[$entityId][$attr] = '';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param int $storeId
     * @param array $entityIds
     */
    private function collectIds(int $storeId, array $entityIds = []): void
    {
        $this->entityIds = $this->filter->execute(
            $this->contentConfigRepository->getFilters($storeId)
        );
        if ($entityIds) {
            $this->entityIds = array_intersect($entityIds, $this->entityIds);
        }
    }

    /**
     * Attritbute collector
     *
     * @param int $storeId
     */
    private function collectAttributes(int $storeId = 0): void
    {
        $this->attributeMap += $this->contentConfigRepository->getAttributes($storeId);

        $extraFields = $this->contentConfigRepository->getExtraFields($storeId);
        foreach ($extraFields as $field) {
            $this->attributeMap[$field['name']] = $field['attribute'];
            $this->resultMap[$field['name']] = $field['attribute'];
        }

        $advancedFilters = $this->contentConfigRepository->getAdvancedFilters($storeId);
        foreach ($advancedFilters as $filter) {
            $this->attributeMap[] = $filter['attribute'];
        }

        $this->attributeMap = array_filter($this->attributeMap);
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function collectProductData(int $storeId): array
    {
        $productsBehaviour = [
            'configurable' => $this->contentConfigRepository->getConfigProductsBehaviour($storeId),
            'bundle' => $this->contentConfigRepository->getBundleProductsBehaviour($storeId),
            'grouped' => $this->contentConfigRepository->getGroupedProductsBehaviour($storeId)
        ];
        $extraParameters = [
            'advanced' => [
                'inventory' => $this->contentConfigRepository->getInventory($storeId),
                'inventory_fields' => $this->contentConfigRepository->getInventoryFields($storeId),
                'advanced_filters' => $this->contentConfigRepository->getAdvancedFilters($storeId)
            ]
        ];

        return $this->type->execute(
            $this->entityIds,
            $this->attributeMap,
            $productsBehaviour,
            $extraParameters,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @param int $entityId
     * @param array $item
     */
    private function addImageData(int $storeId, int $entityId, array &$item)
    {
        $imageData = $this->imageData[$entityId] ?? null;
        if ($imageData === null) {
            return;
        }

        $imageConfig = $this->contentConfigRepository->getImageAttributes($storeId);
        if (!isset($imageData[$storeId])) {
            $storeId = 0;
        }

        ksort($imageData[$storeId]);
        if (count($imageConfig) == 1) {
            foreach ($imageData[$storeId] as $image) {
                if (in_array($imageConfig['image'], $image['types'])) {
                    $item['image'] = $image['file'];
                }
            }
        } else {
            $item['image'] = null;
            foreach ($imageData[$storeId] as $index => $image) {
                if ($item['image'] === null) {
                    $item['image'] = $image['file'];
                } else {
                    $item['additional_images'][] = $image['file'];
                }
            }
        }
    }
}
