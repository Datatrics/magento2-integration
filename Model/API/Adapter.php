<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\API;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Api\API\AdapterInterface;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;

/**
 * Class ProfileAdd
 *
 * API connection class
 */
class Adapter implements AdapterInterface
{

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $projectId;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * Constructor.
     *
     * @param Curl $curl
     * @param Json $json
     * @param ConfigRepository $configRepository
     * @param LogRepository $logRepository
     */
    public function __construct(
        Curl $curl,
        Json $json,
        ConfigRepository $configRepository,
        LogRepository $logRepository
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->configRepository = $configRepository;
        $this->apiKey = $configRepository->getApiKey();
        $this->projectId = $configRepository->getProjectId();
        $this->logRepository = $logRepository;
    }

    /**
     * @param array $method
     * @param null $id
     *
     * @return string
     */
    private function getUrl($method, $id = null)
    {
        if ($method == self::CREATE_INTEGRATION) {
            return sprintf(
                self::TOKEN_URL,
                $this->projectId,
                $this->getMethodUrl($method, $id)
            );
        }

        return sprintf(
            self::GENERAL_URL,
            $this->projectId,
            $this->getMethodUrl($method, $id)
        );
    }

    /**
     * @inheritDoc
     */
    public function setCredentials($apiKey, $projectId)
    {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
    }

    /**
     * @param array $method
     * @param int|string|null $id
     *
     * @return mixed|string
     */
    private function getMethodUrl(array $method, $id = null)
    {
        if ($id) {
            return sprintf($method['key'], $id);
        }
        return $method['key'];
    }

    /**
     * @inheritDoc
     */
    public function execute($method, $id = null, $data = null)
    {
        $validation = $this->validate($method['require'], (bool)$id, (bool)$data);
        if (!$validation['success']) {
            return $validation;
        }
        $this->curl->setHeaders($this->getHttpHeaders());
        $this->makeRequest($method, $id, $data);
        return $this->processResult();
    }

    /**
     * @param array $method
     * @param null $id
     * @param null $data
     */
    private function makeRequest($method, $id = null, $data = null)
    {
        $this->logRepository->addDebugLog('Request', [$this->getUrl($method), $data]);
        switch ($method) {
            case self::GET_PROFILES:
            case self::GET_CONVERSIONS:
            case self::GET_INTERACTIONS:
            case self::GET_CONTENTS:
                $this->curl->get($this->getUrl($method));
                break;
            case self::GET_PROFILE:
            case self::GET_CONVERSION:
            case self::GET_INTERACTION:
            case self::GET_CONTENT:
                $this->curl->get($this->getUrl($method, $id));
                break;
            case self::CREATE_PROFILE:
            case self::CREATE_CONVERSION:
            case self::CREATE_INTERACTION:
            case self::CREATE_CONTENT:
            case self::BULK_CREATE_PROFILE:
            case self::BULK_CREATE_CONVERSION:
            case self::BULK_CREATE_INTERACTION:
            case self::BULK_CREATE_CONTENT:
            case self::BULK_CREATE_CATEGORIES:
            case self::CREATE_INTEGRATION:
                $this->curl->post($this->getUrl($method), $data);
                break;
            case self::UPDATE_PROFILE:
            case self::UPDATE_CONTENT:
                /** @phpstan-ignore-next-line */
                $this->curl->put($this->getUrl($method, $id), $data);
                break;
            case self::DELETE_CONTENT:
                /** @phpstan-ignore-next-line */
                $this->curl->del($this->getUrl($method, $id));
                break;
        }
    }

    /**
     * Define the HTTP headers
     *
     * @return array
     */
    private function getHttpHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'Datatrics/API 2.0',
            'X-apikey' => $this->apiKey,
            'X-client-name' => 'Datatrics/API 2.0',
            'X-datatrics-client-info' => php_uname()
        ];
    }

    /**
     * @return array
     */
    private function processResult(): array
    {
        $result = [];
        if ($this->curl->getBody()) {
            try {
                $result = $this->json->unserialize($this->curl->getBody());
            } catch (\Exception $e) {
                $this->logRepository->addErrorLog('Result unserialize', $e->getMessage());
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        if (array_key_exists('message', $result)) {
            $message = $result['message'];
        } else {
            $message = null;
        }
        switch ($this->curl->getStatus()) {
            case 100:
            case 200:
            case 201:
            case 204:
                $this->logRepository->addDebugLog('Response', $result);
                return [
                    'success' => true,
                    'message' => '',
                    'data' => $result
                ];
            case 500:
                $message = ($message) ? $message : 'Internal server error.';
                break;
            case 403:
                $message = ($message) ? $message : 'Access denied.';
                break;
            case 404:
                $message = ($message) ? $message : 'Endpoint URL is invalid.';
                break;
            case 424:
                $message = ($message)
                    ? $message
                    : 'The request failed because it depended on another request and that request failed.';
                break;
            default:
                if (!array_key_exists('error', $result)) {
                    $message = $result['message'];
                } else {
                    $message = $result['error']['message'];
                }
                break;
        }
        $this->logRepository->addErrorLog('API call error', $message);
        return [
            'success' => false,
            'message' => $message
        ];
    }

    /**
     * @param array $require
     * @param bool $hasId
     * @param bool $hasData
     *
     * @return array
     */
    private function validate(array $require, bool $hasId, bool $hasData): array
    {
        if (in_array('id', $require) && !$hasId) {
            return [
                'success' => false,
                'message' => 'Please set ID'
            ];
        }
        if (in_array('data', $require) && !$hasData) {
            return [
                'success' => false,
                'message' => 'Please set data'
            ];
        }
        return [
            'success' => true,
            'message' => ''
        ];
    }
}
