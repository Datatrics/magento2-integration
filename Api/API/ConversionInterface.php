<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\API;

/**
 * API Conversion interface
 */
interface ConversionInterface extends InteractionInterface
{

    /**
     * Method data
     */
    public const GET_CONVERSIONS = [
        'key' => 'sale',
        'method' => 'GET',
        'require' => []
    ];

    /**
     * Method data
     */
    public const GET_CONVERSION = [
        'key' => 'sale/%s',
        'method' => 'GET',
        'require' => ['id']
    ];

    /**
     * Method data
     */
    public const CREATE_CONVERSION = [
        'key' => 'sale',
        'method' => 'POST',
        'require' => ['data']
    ];

    /**
     * Method data
     */
    public const BULK_CREATE_CONVERSION = [
        'key' => 'sale/bulk',
        'method' => 'POST',
        'require' => ['data']
    ];
}
