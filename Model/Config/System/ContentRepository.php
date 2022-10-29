<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Config\System;

use Datatrics\Connect\Api\Config\System\ContentInterface;
use Datatrics\Connect\Model\Config\Repository as ConfigRepository;

/**
 * Content provider class
 */
class ContentRepository extends ConfigRepository implements ContentInterface
{

    /**
     * @inheritDoc
     */
    public function isEnabled(int $storeId = null): bool
    {
        if (!parent::isEnabled($storeId)) {
            return false;
        }

        return $this->isSetFlag(self::XML_PATH_PRODUCT_SYNC_ENABLED, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(int $storeId): array
    {
        return [
                'sku' => $this->getSkuAttribute($storeId),
                'name' => $this->getNameAttribute($storeId),
                'description' => $this->getDescriptionAttribute($storeId),
                'short_description' => $this->getShortDescriptionAttribute($storeId),
            ] + $this->getImageAttributes($storeId);
    }

    /**
     * Get selected attribute for 'sku'
     *
     * @param int $storeId
     *
     * @return string
     */
    private function getSkuAttribute(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_SKU, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getProcessingLimit(int $storeId): int
    {
        return (int)$this->getStoreValue(self::XML_PATH_LIMIT, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getProcessingLimitAdd(): int
    {
        return (int)$this->getStoreValue(self::XML_PATH_LIMIT_ADD);
    }

    /**
     * Get selected attribute for 'name'
     *
     * @param int $storeId
     *
     * @return string
     */
    private function getNameAttribute(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_NAME, $storeId);
    }

    /**
     * Get selected attribute for 'description'
     *
     * @param int $storeId
     *
     * @return string
     */
    private function getDescriptionAttribute(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_DESCRIPTION, $storeId);
    }

    /**
     * Get selected attribute for 'short description'
     *
     * @param int $storeId
     *
     * @return string
     */
    private function getShortDescriptionAttribute(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_SHORT_DESCRIPTION, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getImageAttributes(int $storeId): array
    {
        $source = $this->getStoreValue(self::XML_PATH_PRODUCT_IMAGE, $storeId);
        if ($source == 'all') {
            return [
                'image' => 'image',
                'small_image' => 'small_image',
                'thumbnail' => 'thumbnail',
                'swatch_image' => 'swatch_image',
            ];
        } else {
            return ['image' => $source];
        }
    }

    /**
     * @inheritDoc
     */
    public function getExtraFields(int $storeId = null): array
    {
        return $this->getStoreValueArray(self::XML_PATH_EXTRA_FIELDS, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getConfigProductsBehaviour(int $storeId): array
    {
        return [
            'use' => $this->configurableProductLogic($storeId),
            'use_parent_url' => $this->configurableProductUrl($storeId),
            'use_parent_images' => $this->configurableProductImage($storeId),
            'use_parent_attributes' => $this->configurableParentAttributes($storeId),
            'use_non_visible_fallback' => $this->configurableNonVisibleFallback($storeId)
        ];
    }

    /**
     * Logic for 'configurable' products
     *
     * @param int $storeId
     *
     * @return string
     * @see \Datatrics\Connect\Model\Source\Configurable\Options
     */
    private function configurableProductLogic(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_USE_CONFIG_PRODUCTS, $storeId);
    }

    /**
     * Logic for 'configurable' product links
     *
     * @param int $storeId
     *
     * @return string
     */
    private function configurableProductUrl(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_CONFIG_USE_PARENT_URL_FOR_SIMPLES, $storeId);
    }

    /**
     * Logic for 'configurable' product image
     *
     * @param int $storeId
     *
     * @return int
     * @see \Datatrics\Connect\Model\Source\Configurable\Image
     */
    private function configurableProductImage(int $storeId): int
    {
        return (int)$this->getStoreValue(self::XML_PATH_CONFIG_USE_PARENT_IMAGES_FOR_SIMPLES, $storeId);
    }

    /**
     * Attributes that should be forced to get data from parent 'configurable' product
     *
     * @param int $storeId
     *
     * @return array
     */
    private function configurableParentAttributes(int $storeId): array
    {
        $attributes = $this->getStoreValue(self::XML_PATH_CONFIG_USE_PARENT_DATA_FOR_SIMPLES, $storeId);
        return $attributes ? explode(',', $attributes) : [];
    }

    /**
     * Flag to only use fallback to parent 'configurable' attributes on non visible parents
     *
     * @param int $storeId
     *
     * @return bool
     */
    private function configurableNonVisibleFallback(int $storeId): bool
    {
        return $this->isSetFlag(self::XML_PATH_CONFIG_USE_NON_VISIBLE_FALLBACK, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getBundleProductsBehaviour(int $storeId = null): array
    {
        return [
            'use' => $this->bundleProductLogic($storeId),
            'use_parent_url' => $this->bundleProductUrl($storeId),
            'use_parent_images' => $this->bundleProductImage($storeId),
            'use_parent_attributes' => $this->bundleParentAttributes($storeId),
            'use_non_visible_fallback' => $this->bundleNonVisibleFallback($storeId)
        ];
    }

    /**
     * Logic for 'bundle' products
     *
     * @param int $storeId
     *
     * @return string
     * @see \Datatrics\Connect\Model\Source\Bundle\Options
     */
    private function bundleProductLogic(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_USE_BUNDLE_PRODUCTS, $storeId);
    }

    /**
     * Logic for 'bundle' product links
     *
     * @param int $storeId
     *
     * @return string
     */
    private function bundleProductUrl(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_BUNDLE_USE_PARENT_URL_FOR_SIMPLES, $storeId);
    }

    /**
     * Logic for 'bundle' product image
     *
     * @param int $storeId
     *
     * @return int
     * @see \TradeTracker\Connect\Model\Config\System\Source\Bundle\Image
     */
    private function bundleProductImage(int $storeId): int
    {
        return (int)$this->getStoreValue(self::XML_PATH_BUNDLE_USE_PARENT_IMAGES_FOR_SIMPLES, $storeId);
    }

    /**
     * Attributes that should be forced to get data from parent 'bundle' product
     *
     * @param int $storeId
     *
     * @return array
     */
    private function bundleParentAttributes(int $storeId): array
    {
        $attributes = $this->getStoreValue(self::XML_PATH_BUNDLE_USE_PARENT_DATA_FOR_SIMPLES, $storeId);
        return $attributes ? explode(',', $attributes) : [];
    }

    /**
     * Flag to only use fallback to parent 'bundle' attributes on non visible parents
     *
     * @param int $storeId
     *
     * @return bool
     */
    private function bundleNonVisibleFallback(int $storeId): bool
    {
        return $this->isSetFlag(self::XML_PATH_BUNDLE_USE_NON_VISIBLE_FALLBACK, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getGroupedProductsBehaviour(int $storeId): array
    {
        return [
            'use' => $this->groupedProductLogic($storeId),
            'use_parent_url' => $this->groupedProductUrl($storeId),
            'use_parent_images' => $this->groupedProductImage($storeId),
            'use_parent_attributes' => $this->groupedParentAttributes($storeId),
            'use_non_visible_fallback' => $this->groupedNonVisibleFallback($storeId),
        ];
    }

    /**
     * Logic for 'grouped' products
     *
     * @param int $storeId
     *
     * @return string
     * @see \Datatrics\Connect\Model\Source\Grouped\Options
     */
    public function groupedProductLogic(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_USE_GROUPED_PRODUCTS, $storeId);
    }

    /**
     * Logic for 'grouped' product links
     *
     * @param int $storeId
     *
     * @return string
     */
    public function groupedProductUrl(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_GROUPED_USE_PARENT_URL_FOR_SIMPLES, $storeId);
    }

    /**
     * Logic for 'grouped' product image
     *
     * @param int $storeId
     *
     * @return int
     * @see \Datatrics\Connect\Model\Source\Grouped\Image
     */
    public function groupedProductImage(int $storeId): int
    {
        return (int)$this->getStoreValue(self::XML_PATH_GROUPED_USE_PARENT_IMAGES_FOR_SIMPLES, $storeId);
    }

    /**
     * Attributes that should be forced to get data from parent 'grouped' product
     *
     * @param int $storeId
     *
     * @return array
     */
    public function groupedParentAttributes(int $storeId): array
    {
        $attributes = $this->getStoreValue(self::XML_PATH_GROUPED_USE_PARENT_DATA_FOR_SIMPLES, $storeId);
        return $attributes ? explode(',', $attributes) : [];
    }

    /**
     * Flag to only use fallback to parent 'grouped' attributes on non visible parents
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function groupedNonVisibleFallback(int $storeId): bool
    {
        return $this->isSetFlag(self::XML_PATH_GROUPED_USE_NON_VISIBLE_FALLBACK, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getFilters(int $storeId): array
    {
        return [
            'add_disabled_products' => $this->addDisableProducts($storeId),
            'filter_by_visibility' => $this->restricProductFeedByVisibility($storeId),
            'visibility' => $this->productFeedVisibilityRestrictions($storeId),
            'restrict_by_category' => $this->restrictProductFeedByCategory($storeId),
            'category_restriction_behaviour' => $this->categoryRestrictionsFilterType($storeId),
            'category' => $this->getCategoryIds($storeId),
            'exclude_out_of_stock' => $this->excludeOutOfStock($storeId),
        ];
    }

    private function addDisableProducts(int $storeId): bool
    {
        return $this->isSetFlag(self::XML_PATH_FILTER_BY_STATUS, $storeId);
    }

    /**
     * Restrict by 'visibility'
     *
     * @param int $storeId
     *
     * @return bool
     */
    private function restricProductFeedByVisibility(int $storeId): bool
    {
        return $this->isSetFlag(self::XML_PATH_FILTER_BY_VISIBILITY, $storeId);
    }

    /**
     * Only add products with these following Visibility
     *
     * @param int $storeId
     *
     * @return array
     */
    private function productFeedVisibilityRestrictions(int $storeId): array
    {
        $visibility = $this->getStoreValue(self::XML_PATH_VISIBILITY, $storeId);
        return $visibility ? explode(',', $visibility) : [];
    }

    /**
     * Restrict by 'category'
     *
     * @param int $storeId
     *
     * @return bool
     */
    private function restrictProductFeedByCategory(int $storeId): bool
    {
        return $this->isSetFlag(self::XML_PATH_RESTRICT_BY_CATEGORY, $storeId);
    }

    /**
     * Get category restriction filter type
     *
     * @param int $storeId
     *
     * @return string
     * @see \Datatrics\Connect\Model\Source\CategoryTypeList
     */
    private function categoryRestrictionsFilterType(int $storeId): string
    {
        return $this->getStoreValue(self::XML_PATH_EXCLUDE_OR_INCLUDE_BY_CATEGORY, $storeId);
    }

    /**
     * Only add/remove products that belong to these categories
     *
     * @param int $storeId
     *
     * @return array
     */
    private function getCategoryIds(int $storeId): array
    {
        $categoryIds = $this->getStoreValue(self::XML_PATH_CATEGORY, $storeId);
        return $categoryIds ? explode(',', $categoryIds) : [];
    }

    /**
     * Exclude of of stock products
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function excludeOutOfStock(int $storeId): bool
    {
        return $this->isSetFlag(self::XML_PATH_EXCLUDE_OUT_OF_STOCK, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getInventory(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_INVENTORY, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getInventoryFields(int $storeId = null): array
    {
        $fields = $this->getStoreValue(self::XML_PATH_INVENTORY_FIELDS, $storeId);
        return $fields ? explode(',', $fields) : [];
    }

    /**
     * @inheritDoc
     */
    public function getAdvancedFilters(int $storeId): array
    {
        return $this->getStoreValueArray(self::XML_PATH_FILTERS_DATA, $storeId);
    }
}
