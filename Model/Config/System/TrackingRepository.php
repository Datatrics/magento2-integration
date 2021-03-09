<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Config\System;

use Datatrics\Connect\Api\Config\System\TrackingInterface;
use Datatrics\Connect\Model\Config\Repository as ConfigRepository;

/**
 * Tracking provider class
 */
class TrackingRepository extends ConfigRepository implements TrackingInterface
{

    /**
     * @inheritDoc
     */
    public function isEnabled(int $storeId = null): bool
    {
        if (!parent::isEnabled($storeId)) {
            return false;
        }

        return $this->isSetFlag(self::XML_PATH_TRACKING_ENABLED);
    }
}
