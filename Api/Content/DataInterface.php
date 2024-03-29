<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Content;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for Datatrics content
 * @api
 */
interface DataInterface extends ExtensibleDataInterface
{

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const STORE_ID = 'store_id';
    public const PRODUCT_ID = 'product_id';
    public const PARENT_ID = 'parent_id';
    public const UPDATED_AT = 'updated_at';
    public const UPDATE_MSG = 'update_msg';
    public const UPDATE_ATTEMPTS = 'update_attempts';
    public const STATUS = 'status';

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId(int $productId): self;

    /**
     * @return ?int
     */
    public function getParentId(): ?int;

    /**
     * @param ?int $parentId
     * @return $this
     */
    public function setParentId(?int $parentId): self;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): self;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self;

    /**
     * @return string
     */
    public function getUpdateMsg(): string;

    /**
     * @param string $updateMsg
     * @return $this
     */
    public function setUpdateMsg(string $updateMsg): self;

    /**
     * @return int
     */
    public function getUpdateAttempts(): int;

    /**
     * @param int $updateAttempts
     * @return $this
     */
    public function setUpdateAttempts(int $updateAttempts): self;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self;
}
