<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Content;

use Datatrics\Connect\Api\Content\DataInterface as ContentData;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Datatrics Content data class
 */
class Data extends AbstractModel implements ExtensibleDataInterface, ContentData
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
    public function getProductId(): int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId(int $productId): ContentData
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): ?int
    {
        return $this->getData(self::PARENT_ID) ? (int)$this->getData(self::PARENT_ID) : null;
    }

    /**
     * @inheritDoc
     */
    public function setParentId(?int $parentId): ContentData
    {
        return $this->setData(self::PARENT_ID, $parentId);
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
    public function setStoreId(int $storeId): ContentData
    {
        return $this->setData(self::STORE_ID, $storeId);
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
    public function setUpdatedAt(string $updatedAt): ContentData
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
    public function setUpdateMsg(string $updateMsg): ContentData
    {
        return $this->setData(self::UPDATE_MSG, $updateMsg);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateAttempts(): int
    {
        return (int)$this->getData(self::UPDATE_ATTEMPTS);
    }

    /**
     * @inheritDoc
     */
    public function setUpdateAttempts(int $updateAttempts): ContentData
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
    public function setStatus(string $status): ContentData
    {
        return $this->setData(self::STATUS, $status);
    }
}
