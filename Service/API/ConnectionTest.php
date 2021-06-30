<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\API;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ConnectionTest
 *
 * Test API connection
 */
class ConnectionTest
{

    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;
    /**
     * @var ApiAdapter
     */
    private $adapter;

    /**
     * ConnectionTest constructor.
     * @param ContentConfigRepository $contentConfigRepository
     * @param ApiAdapter $adapter
     */
    public function __construct(
        ContentConfigRepository $contentConfigRepository,
        ApiAdapter $adapter
    ) {
        $this->contentConfigRepository = $contentConfigRepository;
        $this->adapter = $adapter;
    }

    /**
     * Test API Connection by storeId
     *
     * @param int $storeId
     * @throws LocalizedException
     */
    public function executeByStoreId(int $storeId)
    {
        $this->execute(
            $this->contentConfigRepository->getApiKey($storeId),
            $this->contentConfigRepository->getProjectId($storeId)
        );
    }

    /**
     * Test API Connection
     *
     * @param string $apiKey
     * @param string $projectId
     * @throws LocalizedException
     */
    public function execute(string $apiKey, string $projectId)
    {
        try {
            $this->adapter->setCredentials($apiKey, $projectId);
            $success = $this->adapter->execute(ApiAdapter::GET_PROFILES)['success'];
            $exceptionMsg = !$success ? __('Incorrect credentials, please update and try again.') : null;
        } catch (\Exception $exception) {
            $exceptionMsg = __('Connection Issue: ' . $exception->getMessage());
        }

        if ($exceptionMsg) {
            throw new LocalizedException($exceptionMsg);
        }
    }
}
