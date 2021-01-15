<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\API;

use Magento\Framework\HTTP\Client\Curl;

/**
 * Class CurlExtra
 *
 * Provide PUT and DEL methods
 */
class CurlExtra extends Curl
{
    /**
     * Make PUT request
     *
     * @param string $uri
     * @param array|string $params
     * @return void
     *
     */
    public function put($uri, $params)
    {
        $this->makeRequest("PUT", $uri, $params);
    }

    /**
     * Make DEL request
     *
     * @param string $uri
     * @return void
     *
     */
    public function del($uri)
    {
        $this->makeRequest("DELETE", $uri);
    }
}
