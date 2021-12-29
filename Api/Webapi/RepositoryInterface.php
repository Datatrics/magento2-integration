<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Webapi;

/**
 * Webapi repository interface
 */
interface RepositoryInterface
{

    /**
     * Get general info
     *
     * @api
     * @return string
     */
    public function getInformation(): string;
}
