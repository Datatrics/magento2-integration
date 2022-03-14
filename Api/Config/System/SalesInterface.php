<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Config\System;

use Datatrics\Connect\Api\Config\RepositoryInterface;

/**
 * Sales group interface
 */
interface SalesInterface extends RepositoryInterface
{

    /* Sales */
    public const XML_PATH_ORDER_ENABLED = 'datatrics_connect_order/order_sync/enable';
    public const XML_PATH_ORDER_STATE_LIMIT = 'datatrics_connect_order/order_sync/limit_order_state';
    public const XML_PATH_ORDER_STATE = 'datatrics_connect_order/order_sync/order_state';
    public const XML_PATH_ORDER_CUSTOMER_LIMIT = 'datatrics_connect_order/order_sync/limit_customer_group';
    public const XML_PATH_ORDER_CUSTOMER_GROUP = 'datatrics_connect_order/order_sync/customer_group';
    public const XML_PATH_ORDER_CRON = 'datatrics_connect_order/order_sync/cron';

    /**
     * Sales Enable FLag
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getSyncStateRestriction(int $storeId = null): bool;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return array
     */
    public function getSyncState(int $storeId = null): array;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getSyncCustomerRestriction(int $storeId = null): bool;

    /**
     * Get order sync restrictions
     *
     * @param int|null $storeId
     *
     * @return array
     */
    public function getSyncCustomerGroup(int $storeId = null): array;
}
