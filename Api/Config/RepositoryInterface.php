<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Config;

/**
 * Config repository interface
 */
interface RepositoryInterface
{

    const EXTENSION_CODE = 'Datatrics_Connect';

    /* General */
    const XML_PATH_EXTENSION_VERSION = 'datatrics_connect_general/general/version';
    const XML_PATH_EXTENSION_ENABLE = 'datatrics_connect_general/general/enable';
    const XML_PATH_API_KEY = 'datatrics_connect_general/general/api_key';
    const XML_PATH_PROJECT_ID = 'datatrics_connect_general/general/project_id';
    const XML_PATH_SOURCE = 'datatrics_connect_general/general/source';
    const XML_PATH_DEBUG = 'datatrics_connect_general/general/debug';

    /* Content */
    const XML_PATH_SKU = 'datatrics_connect_product/product_data/sku';
    const XML_PATH_NAME = 'datatrics_connect_product/product_data/name';
    const XML_PATH_DESCRIPTION = 'datatrics_connect_product/product_data/description';
    const XML_PATH_SHORT_DESCRIPTION = 'datatrics_connect_product/product_data/short_description';
    const XML_PATH_PRODUCT_IMAGE = 'datatrics_connect_product/product_data/image';
    const XML_PATH_PRODUCT_SYNC_ENABLED = 'datatrics_connect_product/product_sync/enable';
    const XML_PATH_EXTRA_FIELDS = 'datatrics_connect_product/advanced_options/extra_fields';
    const XML_PATH_INVENTORY = 'datatrics_connect_product/advanced_options/inventory';
    const XML_PATH_INVENTORY_FIELDS = 'datatrics_connect_product/advanced_options/inventory_fields';

    /* Content -> Product types: Configurable */
    const XML_PATH_USE_CONFIGURABLE_PRODUCTS
        = 'datatrics_connect_product/types/configurable';
    const XML_PATH_CONFIGURABLE_USE_PARENT_URL_FOR_SIMPLES
        = 'datatrics_connect_product/types/configurable_link';
    const XML_PATH_CONFIGURABLE_USE_PARENT_IMAGES_FOR_SIMPLES
        = 'datatrics_connect_product/types/configurable_image';
    const XML_PATH_CONFIGURABLE_USE_PARENT_DATA_FOR_SIMPLES
        = 'datatrics_connect_product/types/configurable_parent_atts';
    const XML_PATH_CONFIGURABLE_USE_NON_VISIBLE_FALLBACK
        = 'datatrics_connect_product/types/configurable_nonvisible';

    /* Content -> Product types: Bundle */
    const XML_PATH_USE_BUNDLE_PRODUCTS = 'datatrics_connect_product/types/bundle';
    const XML_PATH_BUNDLE_USE_PARENT_URL_FOR_SIMPLES = 'datatrics_connect_product/types/bundle_link';
    const XML_PATH_BUNDLE_USE_PARENT_IMAGES_FOR_SIMPLES = 'datatrics_connect_product/types/bundle_image';
    const XML_PATH_BUNDLE_USE_PARENT_DATA_FOR_SIMPLES = 'datatrics_connect_product/types/bundle_parent_atts';
    const XML_PATH_BUNDLE_USE_NON_VISIBLE_FALLBACK = 'datatrics_connect_product/types/bundle_nonvisible';

    /* Content -> Product types: Grouped */
    const XML_PATH_USE_GROUPED_PRODUCTS = 'datatrics_connect_product/types/grouped';
    const XML_PATH_GROUPED_USE_PARENT_URL_FOR_SIMPLES = 'datatrics_connect_product/types/grouped_link';
    const XML_PATH_GROUPED_USE_PARENT_IMAGES_FOR_SIMPLES = 'datatrics_connect_product/types/grouped_image';
    const XML_PATH_GROUPED_USE_PARENT_DATA_FOR_SIMPLES = 'datatrics_connect_product/types/grouped_parent_atts';
    const XML_PATH_GROUPED_USE_NON_VISIBLE_FALLBACK = 'datatrics_connect_product/types/grouped_nonvisible';

    /* Filters */
    const XML_PATH_ADD_DISABLED_PRODUCTS = 'datatrics_connect_product/product_filter/add_disabled';
    const XML_PATH_FILTER_BY_VISIBILITY = 'datatrics_connect_product/product_filter/visbility_enabled';
    const XML_PATH_VISIBILITY = 'datatrics_connect_product/product_filter/visbility';
    const XML_PATH_RESTRICT_BY_CATEGORY = 'datatrics_connect_product/product_filter/category_enabled';
    const XML_PATH_EXCLUDE_OR_INCLUDE_BY_CATEGORY = 'datatrics_connect_product/product_filter/category_type';
    const XML_PATH_CATEGORY = 'datatrics_connect_product/product_filter/category';
    const XML_PATH_EXCLUDE_OUT_OF_STOCK = 'datatrics_connect_product/product_filter/stock';
    const XML_PATH_FILTERS_DATA = 'datatrics_connect_product/product_filter/filters_data';

    /* Profile */
    const XML_PATH_CUSTOMER_ENABLED = 'datatrics_connect_customer/customer_sync/enable';
    const XML_PATH_CUSTOMER_LIMIT = 'datatrics_connect_customer/customer_sync/limit_customer_group';
    const XML_PATH_CUSTOMER_GROUP = 'datatrics_connect_customer/customer_sync/customer_group';
    const XML_PATH_CUSTOMER_CRON = 'datatrics_connect_customer/customer_sync/cron';
    const XML_PATH_CUSTOMER_CRON_CUSTOM = 'datatrics_connect_customer/customer_sync/cron_custom';

    /* Sales */
    const XML_PATH_ORDER_ENABLED = 'datatrics_connect_order/order_sync/enable';
    const XML_PATH_ORDER_STATE_LIMIT = 'datatrics_connect_order/order_sync/limit_order_state';
    const XML_PATH_ORDER_STATE = 'datatrics_connect_order/order_sync/order_state';
    const XML_PATH_ORDER_CUSTOMER_LIMIT = 'datatrics_connect_order/order_sync/limit_customer_group';
    const XML_PATH_ORDER_CUSTOMER_GROUP = 'datatrics_connect_order/order_sync/customer_group';
    const XML_PATH_ORDER_CRON = 'datatrics_connect_order/order_sync/cron';
    const XML_PATH_ORDER_CRON_CUSTOM = 'datatrics_connect_order/order_sync/cron_custom';

    /* Tracking */
    const XML_PATH_TRACKING_ENABLED = 'datatrics_connect_tracking/tracking/enable';

    /**
     * Get sync filters
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Get configurable products sync behaviour
     *
     * @return array
     */
    public function getConfigProductsBehaviour(): array;

    /**
     * Get bundle products sync behaviour
     *
     * @return array
     */
    public function getBundleProductsBehaviour(): array;

    /**
     * Get grouped products sync behaviour
     *
     * @return array
     */
    public function getGroupedProductsBehaviour(): array;

    /**
     * Get extension version
     *
     * @return string
     */
    public function getExtensionVersion(): string;

    /**
     * Get extension code
     *
     * @return string
     */
    public function getExtensionCode(): string;

    /**
     * Get Magento Version
     *
     * @return string
     */
    public function getMagentoVersion(): string;

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null) : bool;

    /**
     * Check if debug mode is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDebugMode(int $storeId = null) : bool;

    /**
     * Check is tracking enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isTrackingEnabled(int $storeId = null) : bool;

    /**
     * Check is product sync enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isProductSyncEnabled(int $storeId = null) : bool;

    /**
     * Get Product Source for Sync
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSyncSource(int $storeId = null) : string;

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore() : \Magento\Store\Api\Data\StoreInterface;

    /**
     * Get API key
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getApiKey(int $storeId = null) : string;

    /**
     * Get project ID
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getProjectId(int $storeId = null) : string;

    /**
     * Get SKU attribute
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSku(int $storeId = null) : string;

    /**
     * Get name attribute
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getName(int $storeId = null) : string;

    /**
     * Get filters data
     *
     * @param int|null $storeId
     *
     * @return array
     */
    public function getFiltersData(int $storeId = null): array;

    /**
     * Get description attribute
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getDescription(int $storeId = null) : string;

    /**
     * Get short description attribute
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getShortDescription(int $storeId = null) : string;

    /**
     * Get product image attribute
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getImage(int $storeId = null): string;

    /**
     * Get customer sync status
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCustomerSyncEnabled(int $storeId = null) : string;

    /**
     * Get customer sync restrictions
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCustomerSyncRestriction(int $storeId = null) : string;

    /**
     * Get customer sync restrictions
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCustomerSyncGroup(int $storeId = null) : string;

    /**
     * Get customer sync cron
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCustomerSyncCron(int $storeId = null) : string;

    /**
     * Get customer sync cron custom
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCustomerSyncCronCustom(int $storeId = null) : string;

    /**
     * Get order sync status
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderSyncEnabled(int $storeId = null) : string;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderSyncStateRestriction(int $storeId = null) : string;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderSyncState(int $storeId = null) : string;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderSyncCustomerRestriction(int $storeId = null) : string;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderSyncCustomerGroup(int $storeId = null) : string;

    /**
     * Get order sync cron
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderSyncCron(int $storeId = null) : string;

    /**
     * Get order sync cron custom
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderSyncCronCustom(int $storeId = null) : string;

    /**
     * Get extra fields to sync
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getExtraFields(int $storeId = null) : string;

    /**
     * Get inventory
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getInventory(int $storeId = null) : string;

    /**
     * Get inventory fields
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getInventoryFields(int $storeId = null) : string;
}
