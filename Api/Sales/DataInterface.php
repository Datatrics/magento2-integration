<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Sales;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for Datatrics sales
 * @api
 */
interface DataInterface extends ExtensibleDataInterface
{

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const ORDER_ID = 'order_id';
    public const STORE_ID = 'store_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const UPDATE_MSG = 'update_msg';
    public const UPDATE_ATTEMPTS = 'update_attempts';
    public const STATUS = 'status';
    public const PROFILE_ID = 'profile_id';
    public const EMAIL = 'email';
    public const ITEMS = 'items';
    public const TOTAL = 'total';
    /**#@-*/

    /**
     * @return int
     */
    public function getOrderId() : int;

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId(int $orderId) : self;

    /**
     * @return int
     */
    public function getStoreId() : int;

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId) : self;

    /**
     * @return string
     */
    public function getCreatedAt() : string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt) : self;

    /**
     * @return string
     */
    public function getUpdatedAt() : string;

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt) : self;

    /**
     * @return string
     */
    public function getUpdateMsg() : string;

    /**
     * @param string $updateMsg
     * @return $this
     */
    public function setUpdateMsg(string $updateMsg) : self;

    /**
     * @return string
     */
    public function getUpdateAttempts() : string;

    /**
     * @param int $updateAttempts
     * @return $this
     */
    public function setUpdateAttempts(int $updateAttempts) : self;

    /**
     * @return string
     */
    public function getStatus() : string;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status) : self;

    /**
     * @return string
     */
    public function getProfileId() : string;

    /**
     * @param string $profileId
     * @return $this
     */
    public function setProfileId(string $profileId) : self;

    /**
     * @return string
     */
    public function getEmail() : string;

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email) : self;

    /**
     * @return string
     */
    public function getItems() : string;

    /**
     * @param string $items
     * @return $this
     */
    public function setItems(string $items) : self;

    /**
     * @return string
     */
    public function getTotal() : string;

    /**
     * @param float $total
     * @return $this
     */
    public function setTotal(float $total) : self;
}
