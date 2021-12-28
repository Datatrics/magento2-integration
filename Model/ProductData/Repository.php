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
        'name' => 'name',
        'parent_id' => 'parent_id',
        'short_description' => 'short_description',
        'description' => 'description',
        'url' => 'url',
        'sku' => 'sku',
        'price' => 'price',
        'special_price' => 'sales_price',
        'min_price' => 'min_price',
        'max_price' => 'max_price',
        'updated' => 'updated_at',
        'created' => 'created_at',
        'type' => 'type_id',
        'image' => 'image',
        'additional_images' => 'additional_images',
        'categories' => 'category',
        'stock' => 'qty',
        'min_sale_qty' => 'min_sale_qty',
        'qty_increments' => 'qty_increments',
        'manage_stock' => 'manage_stock',
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

        $result = [];
        foreach ($this->collectProductData($storeId) as $entityId => $productData) {
            $this->addImageData($storeId, $entityId, $productData);
            if (isset($productData['category'])) {
                $this->addCategoryData($productData);
            }
            foreach ($this->resultMap as $index => $attr) {
                $result[$entityId][$index] = $productData[$attr] ?? '';
                if (!$result[$entityId][$index]) {
                    $result[$entityId][$index] = $productData[$index] ?? '';
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
            $this->contentConfigRepository->getFilters($storeId),
            $storeId
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
        $extraParameters = [
            'filters' => [
                'custom' => $this->contentConfigRepository->getAdvancedFilters($storeId)
            ],
            'category' => [
                'add_url' => true,
            ],
            'stock' => [
                'inventory' => $this->contentConfigRepository->getInventory($storeId),
                'inventory_fields' => $this->contentConfigRepository->getInventoryFields($storeId),
            ],
            'behaviour' => [
                'configurable' => $this->contentConfigRepository->getConfigProductsBehaviour($storeId),
                'bundle' => $this->contentConfigRepository->getBundleProductsBehaviour($storeId),
                'grouped' => $this->contentConfigRepository->getGroupedProductsBehaviour($storeId)
            ]
        ];

        return $this->type->execute(
            $this->entityIds,
            $this->attributeMap,
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

    /**
     * Add category data to productData array
     *
     * @param array $productData
     */
    private function addCategoryData(array &$productData): void
    {
        foreach ($productData['category'] as &$category) {
            $category['name'] = $category['path'];
            $category['categoryid'] = $category['category_id'];
            unset($category['path']);
            unset($category['level']);
            unset($category['category_id']);
        }
    }
}
