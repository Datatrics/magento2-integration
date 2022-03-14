<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Config\System;

use Datatrics\Connect\Api\Config\RepositoryInterface;

/**
 * Tracking group interface
 */
interface TrackingInterface extends RepositoryInterface
{

    /* Tracking */
    public const XML_PATH_TRACKING_ENABLED = 'datatrics_connect_tracking/tracking/enable';

    /**
     * Tracking Enable FLag
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool;
}
