<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\API;

/**
 * API Integration interface
 */
interface IntegrationInterface
{

    /**
     * Integration API URL
     */
    const TOKEN_URL = 'https://api-v3.datatrics.com/project/%s/%s/magmodules/connect';

    /**
     * Method data
     */
    const CREATE_INTEGRATION = [
        'key' => 'integrations',
        'method' => 'POST',
        'require' => ['data']
    ];
}
