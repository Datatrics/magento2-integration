<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Content;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Model\AbstractModel;
use Datatrics\Connect\Api\Content\DataInterface as ContentData;

/**
 * Datatrics Content data class
 *
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
    public function getContentId(): string
    {
        return $this->getData(self::CONTENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setContentId(string $contentId): ContentData
    {
        return $this->setData(self::CONTENT_ID, $contentId);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): string
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setParentId(string $parentId): ContentData
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): int
    {
        return $this->getData(self::STORE_ID);
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
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): ContentData
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
    public function getUpdateAttempts(): string
    {
        return $this->getData(self::UPDATE_ATTEMPTS);
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
