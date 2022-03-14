<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\API;

/**
 * API Adapter interface
 */
interface AdapterInterface extends ContentInterface
{

    /**
     * General API URL
     */
    public const GENERAL_URL = 'https://api.datatrics.com/2.0/project/%s/%s';

    /**
     * @param array $method
     * @param null $id
     * @param null $data
     *
     * @return array
     */
    public function execute($method, $id = null, $data = null);

    /**
     * Override credentials
     *
     * @param string $apiKey
     * @param string $projectId
     */
    public function setCredentials($apiKey, $projectId);
}
