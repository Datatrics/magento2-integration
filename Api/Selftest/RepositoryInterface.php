<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Selftest;

/**
 * Selftest repository interface
 */
interface RepositoryInterface
{

    /**
     * Cron delay value
     */
    public const CRON_DELAY = 3600;

    /**
     * List of required php modules
     */
    public const REQUIRED_PHP_MODULES = [
    ];

    /**
     * Test everything
     *
     * @param bool $output
     * @return array
     */
    public function test($output = true): array;
}
