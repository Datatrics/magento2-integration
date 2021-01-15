<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\API;

/**
 * API Interaction interface
 */
interface InteractionInterface extends ProfileInterface
{

    /**
     * Method data
     */
    const GET_INTERACTIONS = [
        'key' => 'interaction',
        'method' => 'GET',
        'require' => []
    ];

    /**
     * Method data
     */
    const GET_INTERACTION = [
        'key' => 'interaction/%s',
        'method' => 'GET',
        'require' => ['id']
    ];

    /**
     * Method data
     */
    const CREATE_INTERACTION = [
        'key' => 'interaction',
        'method' => 'POST',
        'require' => ['data']
    ];

    /**
     * Method data
     */
    const BULK_CREATE_INTERACTION = [
        'key' => 'interaction/bulk',
        'method' => 'POST',
        'require' => ['data']
    ];
}
