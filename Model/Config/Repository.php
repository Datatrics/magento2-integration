<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Setup\Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepositoryInterface;

/**
 * Config repository class
 */
class Repository implements ConfigRepositoryInterface
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    /**
     * Repository constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        ProductMetadataInterface $metadata
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensionVersion(): string
    {
        return $this->getStoreValue(self::XML_PATH_EXTENSION_VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(int $storeId = null): bool
    {
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getFlag(
            self::XML_PATH_EXTENSION_ENABLE,
            $storeId,
            $scope
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getStore() : \Magento\Store\Api\Data\StoreInterface
    {
        try {
            return $this->storeManager->getStore();
        } catch (\Exception $e) {
            if ($store = $this->storeManager->getDefaultStoreView()) {
                return $store;
            }
        }
        $stores = $this->storeManager->getStores();
        return reset($stores);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionCode(): string
    {
        return self::EXTENSION_CODE;
    }

    /**
     * {@inheritDoc}
     */
    public function getMagentoVersion() : string
    {
        return $this->metadata->getVersion();
    }

    /**
     * Get Configuration data
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     *
     * @return string
     */
    private function getStoreValue(
        string $path,
        int $storeId = null,
        string $scope = null
    ) : string {
        if (!$storeId) {
            $storeId = (int)$this->getStore()->getId();
        }
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return (string)$this->scopeConfig->getValue($path, $scope, (int)$storeId);
    }

    /**
     * Get config value flag
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     *
     * @return bool
     */
    private function getFlag(string $path, int $storeId = null, string $scope = null): bool
    {
        if (!$storeId) {
            $storeId = (int)$this->getStore()->getId();
        }
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->isSetFlag($path, $scope, (int)$storeId);
    }

    /**
     * @inheritDoc
     */
    public function isDebugMode(int $storeId = null): bool
    {
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getFlag(
            self::XML_PATH_DEBUG,
            $storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiKey(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_API_KEY,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getProjectId(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_PROJECT_ID,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getSku(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_SKU,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getName(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_NAME,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getDescription(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_DESCRIPTION,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getShortDescription(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_SHORT_DESCRIPTION,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getImage(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_PRODUCT_IMAGE,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getCustomerSyncEnabled(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_CUSTOMER_ENABLED,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getCustomerSyncRestriction(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_CUSTOMER_LIMIT,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getCustomerSyncGroup(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_CUSTOMER_GROUP,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getCustomerSyncCron(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_CUSTOMER_CRON,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getCustomerSyncCronCustom(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_CUSTOMER_CRON_CUSTOM,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderSyncEnabled(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_ORDER_ENABLED,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderSyncStateRestriction(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_ORDER_STATE_LIMIT,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderSyncState(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_ORDER_STATE,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderSyncCustomerRestriction(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_ORDER_CUSTOMER_LIMIT,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderSyncCustomerGroup(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_ORDER_CUSTOMER_GROUP,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderSyncCron(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_ORDER_CRON,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getOrderSyncCronCustom(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_ORDER_CRON_CUSTOM,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getConfigProductsBehaviour(): array
    {
        $storeId = $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return [
            'use' => $this->getStoreValue(
                self::XML_PATH_USE_CONFIGURABLE_PRODUCTS,
                (int)$storeId,
                $scope
            ),
            'use_parent_url' => $this->getStoreValue(
                self::XML_PATH_CONFIGURABLE_USE_PARENT_URL_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_parent_images' => $this->getStoreValue(
                self::XML_PATH_CONFIGURABLE_USE_PARENT_IMAGES_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_parent_attributes' => $this->getStoreValue(
                self::XML_PATH_CONFIGURABLE_USE_PARENT_DATA_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_non_visible_fallback' => $this->getStoreValue(
                self::XML_PATH_CONFIGURABLE_USE_NON_VISIBLE_FALLBACK,
                (int)$storeId,
                $scope
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function getBundleProductsBehaviour(): array
    {
        $storeId = $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return [
            'use' => $this->getStoreValue(
                self::XML_PATH_USE_BUNDLE_PRODUCTS,
                (int)$storeId,
                $scope
            ),
            'use_parent_url' => $this->getStoreValue(
                self::XML_PATH_BUNDLE_USE_PARENT_URL_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_parent_images' => $this->getStoreValue(
                self::XML_PATH_BUNDLE_USE_PARENT_IMAGES_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_parent_attributes' => $this->getStoreValue(
                self::XML_PATH_BUNDLE_USE_PARENT_DATA_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_non_visible_fallback' => $this->getStoreValue(
                self::XML_PATH_BUNDLE_USE_NON_VISIBLE_FALLBACK,
                (int)$storeId,
                $scope
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function getGroupedProductsBehaviour(): array
    {
        $storeId = $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return [
            'use' => $this->getStoreValue(
                self::XML_PATH_USE_GROUPED_PRODUCTS,
                (int)$storeId,
                $scope
            ),
            'use_parent_url' => $this->getStoreValue(
                self::XML_PATH_GROUPED_USE_PARENT_URL_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_parent_images' => $this->getStoreValue(
                self::XML_PATH_GROUPED_USE_PARENT_IMAGES_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_parent_attributes' => $this->getStoreValue(
                self::XML_PATH_GROUPED_USE_PARENT_DATA_FOR_SIMPLES,
                (int)$storeId,
                $scope
            ),
            'use_non_visible_fallback' => $this->getStoreValue(
                self::XML_PATH_GROUPED_USE_NON_VISIBLE_FALLBACK,
                (int)$storeId,
                $scope
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        $storeId = $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return [
            'add_disabled_products' => $this->getStoreValue(
                self::XML_PATH_ADD_DISABLED_PRODUCTS,
                (int)$storeId,
                $scope
            ),
            'filter_by_visibility' => $this->getStoreValue(
                self::XML_PATH_FILTER_BY_VISIBILITY,
                (int)$storeId,
                $scope
            ),
            'visibility' => $this->getStoreValue(
                self::XML_PATH_VISIBILITY,
                (int)$storeId,
                $scope
            ),
            'restrict_by_category' => $this->getStoreValue(
                self::XML_PATH_RESTRICT_BY_CATEGORY,
                (int)$storeId,
                $scope
            ),
            'category_restriction_behaviour' => $this->getStoreValue(
                self::XML_PATH_EXCLUDE_OR_INCLUDE_BY_CATEGORY,
                (int)$storeId,
                $scope
            ),
            'category' => $this->getStoreValue(
                self::XML_PATH_CATEGORY,
                (int)$storeId,
                $scope
            ),
            'exclude_out_of_stock' => $this->getStoreValue(
                self::XML_PATH_EXCLUDE_OUT_OF_STOCK,
                (int)$storeId,
                $scope
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function getExtraFields(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_EXTRA_FIELDS,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getInventory(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_INVENTORY,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getInventoryFields(int $storeId = null): string
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_INVENTORY_FIELDS,
            (int)$storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function isTrackingEnabled(int $storeId = null): bool
    {
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getFlag(
            self::XML_PATH_TRACKING_ENABLED,
            $storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function isProductSyncEnabled(int $storeId = null): bool
    {
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getFlag(
            self::XML_PATH_PRODUCT_SYNC_ENABLED,
            $storeId,
            $scope
        );
    }

    /**
     * @inheritDoc
     */
    public function getProductSyncSource(int $storeId = null): string
    {
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return $this->getStoreValue(
            self::XML_PATH_PRODUCT_SYNC_SOURCE,
            $storeId,
            $scope
        ) ?? 'Magento 2';
    }

    /**
     * @inheritDoc
     */
    public function getFiltersData(int $storeId = null): array
    {
        $storeId = $storeId ? $storeId : $this->getStore()->getId();
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        $filters = $this->getStoreValue(
            self::XML_PATH_FILTERS_DATA,
            (int)$storeId,
            $scope
        );
        try {
            return $this->json->unserialize($filters);
        } catch (\Exception $e) {
            return [];
        }
    }
}
