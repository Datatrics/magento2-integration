<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Config;

use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepositoryInterface;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Config repository class
 */
class Repository implements ConfigRepositoryInterface
{

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
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
     * Get Configuration data
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     *
     * @return string
     */
    protected function getStoreValue(
        string $path,
        int $storeId = null,
        string $scope = null
    ): string {
        if (!$storeId) {
            $storeId = (int)$this->getStore()->getId();
        }
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return (string)$this->scopeConfig->getValue($path, $scope, (int)$storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getStore(int $storeId = null): StoreInterface
    {
        try {
            if ($storeId) {
                return $this->storeManager->getStore($storeId);
            } else {
                return $this->storeManager->getStore();
            }
        } catch (Exception $e) {
            if ($store = $this->storeManager->getDefaultStoreView()) {
                return $store;
            }
        }
        $stores = $this->storeManager->getStores();
        return reset($stores);
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_EXTENSION_ENABLE, $storeId);
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
    protected function isSetFlag(string $path, int $storeId = null, string $scope = null): bool
    {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        if (empty($storeId)) {
            $storeId = $this->getStore()->getId();
        }
        return $this->scopeConfig->isSetFlag($path, $scope, $storeId);
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
    public function getMagentoVersion(): string
    {
        return $this->metadata->getVersion();
    }

    /**
     * @inheritDoc
     */
    public function getApiKey(int $storeId = null): string
    {
        return $this->getStoreValue(self::XML_PATH_API_KEY, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getProjectId(int $storeId = null): string
    {
        return $this->getStoreValue(self::XML_PATH_PROJECT_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getSyncSource(int $storeId = null): string
    {
        return $this->getStoreValue(self::XML_PATH_SOURCE, $storeId) ?? 'Magento 2';
    }

    /**
     * @inheritDoc
     */
    public function isDebugMode(): bool
    {
        return $this->isSetFlag(self::XML_PATH_DEBUG);
    }

    /**
     * Retrieve config value array by path, storeId and scope
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     * @return array
     */
    protected function getStoreValueArray(string $path, int $storeId = null, string $scope = null): array
    {
        $value = $this->getStoreValue($path, (int)$storeId, $scope);

        if (empty($value)) {
            return [];
        }

        try {
            return $this->json->unserialize($value);
        } catch (\Exception $e) {
            return [];
        }
    }
}
