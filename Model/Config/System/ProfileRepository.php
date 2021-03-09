<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Config\System;

use Datatrics\Connect\Api\Config\System\ProfileInterface;
use Datatrics\Connect\Model\Config\Repository as ConfigRepository;

/**
 * Profile provider class
 */
class ProfileRepository extends ConfigRepository implements ProfileInterface
{

    /**
     * @inheritDoc
     */
    public function isEnabled(int $storeId = null): bool
    {
        if (!parent::isEnabled($storeId)) {
            return false;
        }

        return $this->isSetFlag(self::XML_PATH_CUSTOMER_ENABLED, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getSyncRestriction(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_CUSTOMER_LIMIT, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getSyncCustomerGroup(int $storeId = null): array
    {
        $groups = $this->getStoreValue(self::XML_PATH_CUSTOMER_GROUP, $storeId);
        return $groups ? explode(',', $groups) : [];
    }
}
