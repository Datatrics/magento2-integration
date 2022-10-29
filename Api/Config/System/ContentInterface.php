<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Config\System;

use Datatrics\Connect\Api\Config\RepositoryInterface;

/**
 * Content group interface
 */
interface ContentInterface extends RepositoryInterface
{

    /* Content */
    public const XML_PATH_SKU = 'datatrics_connect_product/product_data/sku';
    public const XML_PATH_LIMIT = 'datatrics_connect_product/product_sync/processing_limit';
    public const XML_PATH_LIMIT_ADD = 'datatrics_connect_product/product_sync/processing_limit_add';
    public const XML_PATH_NAME = 'datatrics_connect_product/product_data/name';
    public const XML_PATH_DESCRIPTION = 'datatrics_connect_product/product_data/description';
    public const XML_PATH_SHORT_DESCRIPTION = 'datatrics_connect_product/product_data/short_description';
    public const XML_PATH_PRODUCT_IMAGE = 'datatrics_connect_product/product_data/image';
    public const XML_PATH_PRODUCT_SYNC_ENABLED = 'datatrics_connect_product/product_sync/enable';
    public const XML_PATH_EXTRA_FIELDS = 'datatrics_connect_product/advanced_options/extra_fields';
    public const XML_PATH_INVENTORY = 'datatrics_connect_product/advanced_options/inventory';
    public const XML_PATH_INVENTORY_FIELDS = 'datatrics_connect_product/advanced_options/inventory_fields';

    /* Content -> Product types: Configurable */
    public const XML_PATH_USE_CONFIG_PRODUCTS = 'datatrics_connect_product/types/configurable';
    public const XML_PATH_CONFIG_USE_PARENT_URL_FOR_SIMPLES = 'datatrics_connect_product/types/configurable_link';
    public const XML_PATH_CONFIG_USE_PARENT_IMAGES_FOR_SIMPLES = 'datatrics_connect_product/types/configurable_image';
    public const XML_PATH_CONFIG_USE_PARENT_DATA_FOR_SIMPLES =
        'datatrics_connect_product/types/configurable_parent_atts';
    public const XML_PATH_CONFIG_USE_NON_VISIBLE_FALLBACK = 'datatrics_connect_product/types/configurable_nonvisible';

    /* Content -> Product types: Bundle */
    public const XML_PATH_USE_BUNDLE_PRODUCTS = 'datatrics_connect_product/types/bundle';
    public const XML_PATH_BUNDLE_USE_PARENT_URL_FOR_SIMPLES = 'datatrics_connect_product/types/bundle_link';
    public const XML_PATH_BUNDLE_USE_PARENT_IMAGES_FOR_SIMPLES = 'datatrics_connect_product/types/bundle_image';
    public const XML_PATH_BUNDLE_USE_PARENT_DATA_FOR_SIMPLES = 'datatrics_connect_product/types/bundle_parent_atts';
    public const XML_PATH_BUNDLE_USE_NON_VISIBLE_FALLBACK = 'datatrics_connect_product/types/bundle_nonvisible';

    /* Content -> Product types: Grouped */
    public const XML_PATH_USE_GROUPED_PRODUCTS = 'datatrics_connect_product/types/grouped';
    public const XML_PATH_GROUPED_USE_PARENT_URL_FOR_SIMPLES = 'datatrics_connect_product/types/grouped_link';
    public const XML_PATH_GROUPED_USE_PARENT_IMAGES_FOR_SIMPLES = 'datatrics_connect_product/types/grouped_image';
    public const XML_PATH_GROUPED_USE_PARENT_DATA_FOR_SIMPLES = 'datatrics_connect_product/types/grouped_parent_atts';
    public const XML_PATH_GROUPED_USE_NON_VISIBLE_FALLBACK = 'datatrics_connect_product/types/grouped_nonvisible';

    /* Filters */
    public const XML_PATH_FILTER_BY_STATUS = 'datatrics_connect_product/product_filter/add_disabled';
    public const XML_PATH_FILTER_BY_VISIBILITY = 'datatrics_connect_product/product_filter/visbility_enabled';
    public const XML_PATH_VISIBILITY = 'datatrics_connect_product/product_filter/visbility';
    public const XML_PATH_RESTRICT_BY_CATEGORY = 'datatrics_connect_product/product_filter/category_enabled';
    public const XML_PATH_EXCLUDE_OR_INCLUDE_BY_CATEGORY = 'datatrics_connect_product/product_filter/category_type';
    public const XML_PATH_CATEGORY = 'datatrics_connect_product/product_filter/category';
    public const XML_PATH_EXCLUDE_OUT_OF_STOCK = 'datatrics_connect_product/product_filter/stock';
    public const XML_PATH_FILTERS_DATA = 'datatrics_connect_product/product_filter/filters_data';

    /**
     * Content Enable FLag
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool;

    /**
     * Returns array of attributes
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getAttributes(int $storeId): array;

    /**
     * Get 'image' fields array
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getImageAttributes(int $storeId): array;

    /**
     * Returns array of extra fields
     *
     * @param int|null $storeId
     *
     * @return array
     */
    public function getExtraFields(int $storeId = null): array;

    /**
     * Get inventory
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function getInventory(int $storeId): bool;

    /**
     * Get inventory fields
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getInventoryFields(int $storeId): array;

    /**
     * Get sync filters
     *
     * @param int $storeId
     * @return array
     */
    public function getAdvancedFilters(int $storeId): array;

    /**
     * Get configurable products sync behaviour
     *
     * @param int $storeId
     * @return array
     */
    public function getConfigProductsBehaviour(int $storeId): array;

    /**
     * Get bundle products sync behaviour
     *
     * @param int $storeId
     * @return array
     */
    public function getBundleProductsBehaviour(int $storeId): array;

    /**
     * Get grouped products sync behaviour
     *
     * @param int $storeId
     * @return array
     */
    public function getGroupedProductsBehaviour(int $storeId): array;

    /**
     * Get processing limit
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getProcessingLimit(int $storeId): int;

    /**
     * Get processing limit to add products
     *
     * @return int
     */
    public function getProcessingLimitAdd(): int;
}
