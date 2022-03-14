<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\API;

/**
 * API Content interface
 */
interface ContentInterface extends ConversionInterface
{

    /**
     * Method data
     */
    public const GET_CONTENT = [
        'key' => 'content',
        'method' => 'GET',
        'require' => []
    ];

    /**
     * Method data
     */
    public const GET_CONTENTS = [
        'key' => 'content/%s',
        'method' => 'GET',
        'require' => ['id']
    ];

    /**
     * Method data
     */
    public const CREATE_CONTENT = [
        'key' => 'content?type=item',
        'method' => 'POST',
        'require' => ['data']
    ];

    /**
     * Method data
     */
    public const BULK_CREATE_CONTENT = [
        'key' => 'content/bulk?type=items',
        'method' => 'POST',
        'require' => ['data']
    ];

    /**
     * Method data
     */
    public const BULK_CREATE_CATEGORIES = [
        'key' => 'content/bulk?type=categories',
        'method' => 'POST',
        'require' => ['data']
    ];

    /**
     * Method data
     */
    public const UPDATE_CONTENT = [
        'key' => 'content/%s',
        'method' => 'PUT',
        'require' => ['id', 'data']
    ];

    /**
     * Method data
     */
    public const DELETE_CONTENT = [
        'key' => 'content/%s?type=item',
        'method' => 'DELETE',
        'require' => ['id']
    ];
}
