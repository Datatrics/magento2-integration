<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Config;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Config repository interface
 */
interface RepositoryInterface
{

    /** Extension code */
    public const EXTENSION_CODE = 'Datatrics_Connect';

    /* General */
    public const XML_PATH_EXTENSION_VERSION = 'datatrics_connect_general/general/version';
    public const XML_PATH_EXTENSION_ENABLE = 'datatrics_connect_general/general/enable';
    public const XML_PATH_API_KEY = 'datatrics_connect_general/general/api_key';
    public const XML_PATH_PROJECT_ID = 'datatrics_connect_general/general/project_id';
    public const XML_PATH_SOURCE = 'datatrics_connect_general/general/source';
    public const XML_PATH_DEBUG = 'datatrics_connect_general/general/debug';

    /**
     * Get extension version
     *
     * @return string
     */
    public function getExtensionVersion(): string;

    /**
     * Get Magento Version
     *
     * @return string
     */
    public function getMagentoVersion(): string;

    /**
     * Get extension code
     *
     * @return string
     */
    public function getExtensionCode(): string;

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool;

    /**
     * Get Product Source for Sync
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSyncSource(int $storeId = null): string;

    /**
     * Get current or specified store
     *
     * @param int|null $storeId
     * @return StoreInterface
     */
    public function getStore(int $storeId = null): StoreInterface;

    /**
     * Get API key
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getApiKey(int $storeId = null): string;

    /**
     * Get project ID
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getProjectId(int $storeId = null): string;

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugMode(): bool;
}
