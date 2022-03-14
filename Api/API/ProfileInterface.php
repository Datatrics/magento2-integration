<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\API;

/**
 * API Profile interface
 */
interface ProfileInterface extends IntegrationInterface
{

    /**
     * Method data
     */
    public const GET_PROFILES = [
        'key' => 'profile',
        'method' => 'GET',
        'require' => []
    ];

    /**
     * Method data
     */
    public const GET_PROFILE = [
        'key' => 'profile/%s',
        'method' => 'GET',
        'require' => ['id']
    ];

    /**
     * Method data
     */
    public const CREATE_PROFILE = [
        'key' => 'profile',
        'method' => 'POST',
        'require' => ['data']
    ];

    /**
     * Method data
     */
    public const BULK_CREATE_PROFILE = [
        'key' => 'profile/bulk',
        'method' => 'POST',
        'require' => ['data']
    ];

    /**
     * Method data
     */
    public const UPDATE_PROFILE = [
        'key' => 'profile/%s',
        'method' => 'PUT',
        'require' => ['id', 'data']
    ];
}
