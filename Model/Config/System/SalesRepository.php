<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Config\System;

use Datatrics\Connect\Api\Config\System\SalesInterface;
use Datatrics\Connect\Model\Config\Repository as ConfigRepository;

/**
 * Sales provider class
 */
class SalesRepository extends ConfigRepository implements SalesInterface
{

    /**
     * @inheritDoc
     */
    public function isEnabled(int $storeId = null): bool
    {
        if (!parent::isEnabled($storeId)) {
            return false;
        }

        return $this->isSetFlag(self::XML_PATH_ORDER_ENABLED, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getSyncStateRestriction(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_ORDER_STATE_LIMIT, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getSyncState(int $storeId = null): array
    {
        $states = $this->getStoreValue(self::XML_PATH_ORDER_STATE, $storeId);
        return $states ? explode(',', $states) : [];
    }

    /**
     * @inheritDoc
     */
    public function getSyncCustomerRestriction(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_ORDER_CUSTOMER_LIMIT, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getSyncCustomerGroup(int $storeId = null): array
    {
        $groups = $this->getStoreValue(self::XML_PATH_ORDER_CUSTOMER_GROUP, $storeId);
        return $groups ? explode(',', $groups) : [];
    }
}
