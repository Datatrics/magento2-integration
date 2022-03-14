<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Config\System;

use Datatrics\Connect\Api\Config\RepositoryInterface;

/**
 * Profile group interface
 */
interface ProfileInterface extends RepositoryInterface
{

    /* Profile */
    public const XML_PATH_CUSTOMER_ENABLED = 'datatrics_connect_customer/customer_sync/enable';
    public const XML_PATH_CUSTOMER_LIMIT = 'datatrics_connect_customer/customer_sync/limit_customer_group';
    public const XML_PATH_CUSTOMER_GROUP = 'datatrics_connect_customer/customer_sync/customer_group';
    public const XML_PATH_CUSTOMER_CRON = 'datatrics_connect_customer/customer_sync/cron';
    public const XML_PATH_CUSTOMER_CRON_CUSTOM = 'datatrics_connect_customer/customer_sync/cron_custom';

    /**
     * Profile Enable FLag
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool;

    /**
     * Get customer sync restrictions
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getSyncRestriction(int $storeId = null): bool;

    /**
     * Get customer sync restrictions
     *
     * @param int|null $storeId
     *
     * @return array
     */
    public function getSyncCustomerGroup(int $storeId = null): array;
}
