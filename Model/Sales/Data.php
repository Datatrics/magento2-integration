<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Sales;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Model\AbstractModel;
use Datatrics\Connect\Api\Sales\DataInterface as SalesData;

/**
 * Datatrics Sales data class
 *
 */
class Data extends AbstractModel implements ExtensibleDataInterface, SalesData
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): int
    {
        return (int)$this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(int $orderId): SalesData
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId(int $storeId): SalesData
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): SalesData
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): SalesData
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateMsg(): string
    {
        return $this->getData(self::UPDATE_MSG);
    }

    /**
     * @inheritDoc
     */
    public function setUpdateMsg(string $updateMsg): SalesData
    {
        return $this->setData(self::UPDATE_MSG, $updateMsg);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateAttempts(): string
    {
        return $this->getData(self::UPDATE_ATTEMPTS);
    }

    /**
     * @inheritDoc
     */
    public function setUpdateAttempts(int $updateAttempts): SalesData
    {
        return $this->setData(self::UPDATE_ATTEMPTS, $updateAttempts);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): SalesData
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @param string $profileId
     * @return $this
     */
    public function setProfileId(string $profileId) : SalesData
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * @return string
     */
    public function getProfileId() : string
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email) : SalesData
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string
     */
    public function getItems() : string
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * @param string $items
     * @return $this
     */
    public function setItems(string $items) : SalesData
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @return string
     */
    public function getTotal() : string
    {
        return $this->getData(self::TOTAL);
    }

    /**
     * @param float $total
     * @return $this
     */
    public function setTotal(float $total) : SalesData
    {
        return $this->setData(self::TOTAL, $total);
    }
}
