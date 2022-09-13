<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Integration;

use Datatrics\Connect\Api\API\IntegrationInterface;
use Datatrics\Connect\Model\API\Adapter;
use Datatrics\Connect\Model\Config\Repository as ConfigRepository;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Integration\Api\AuthorizationServiceInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sevice model to create and delete integrations
 */
class Create
{

    public const INTEGRATION_NAME = 'Datatrics Integration';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;
    /**
     * @var OauthServiceInterface
     */
    private $oauthService;
    /**
     * @var AuthorizationServiceInterface
     */
    private $authorizationService;
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ConfigRepository
     */
    private $configRepository;
    /**
     * @var Adapter
     */
    private $adapter;
    /**
     * @var Json
     */
    private $json;

    /**
     * Create constructor.
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     * @param AuthorizationServiceInterface $authorizationService
     * @param CustomerTokenServiceInterface $customerTokenService
     * @param StoreManagerInterface $storeManager
     * @param ConfigRepository $configRepository
     * @param Adapter $adapter
     * @param Json $json
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService,
        AuthorizationServiceInterface $authorizationService,
        CustomerTokenServiceInterface $customerTokenService,
        StoreManagerInterface $storeManager,
        ConfigRepository $configRepository,
        Adapter $adapter,
        Json $json
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->customerTokenService = $customerTokenService;
        $this->storeManager = $storeManager;
        $this->configRepository = $configRepository;
        $this->adapter = $adapter;
        $this->json = $json;
    }

    /**
     * Create a new integration
     *
     * @param int $storeId
     * @return string
     * @throws IntegrationException
     * @throws LocalizedException
     */
    public function createIntegration(int $storeId = 0): string
    {
        $integrationData = [
            'name' => self::INTEGRATION_NAME,
            'endpoint' => 'https://www.datatrics.com/',
            'status' => '1',
            'setup_type' => '0',
        ];
        $integration = $this->integrationService->create($integrationData);
        $integrationId = $integration->getId();
        $customerId = $integration->getConsumerId();
        $this->authorizationService->grantPermissions($integrationId, ['Datatrics_Connect::webapi']);
        $this->oauthService->createAccessToken($customerId, true);
        $token = $this->oauthService->getAccessToken($customerId)->getToken();
        return $this->sendToken($token, $storeId);
    }

    /**
     * Send token & webapi to Datatrics
     *
     * @param string $token
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    private function sendToken(string $token, int $storeId = 0): string
    {
        $apiKey = $this->configRepository->getApiKey($storeId);
        $projectId = $this->configRepository->getProjectId($storeId);
        $data = $this->json->serialize(
            [
                'magento_token' => $token,
                'magento_url' => $this->storeManager->getStore($storeId)->getBaseUrl()
            ]
        );

        if (empty($apiKey) || empty($projectId)) {
            throw new LocalizedException(__('Please set credentials first!'));
        }

        $this->adapter->setCredentials($apiKey, $projectId);
        $result = $this->adapter->execute(IntegrationInterface::CREATE_INTEGRATION, null, $data);

        if ($result['success'] == false && $result['message']) {
            throw new LocalizedException(__('Could not post access token: %1', $result['message']));
        }

        return $token;
    }

    /**
     * Delete current integration
     *
     * @throws IntegrationException
     */
    public function deleteIntegration(): bool
    {
        $integration = $this->integrationService->findByName(self::INTEGRATION_NAME);
        if ($integration->getId()) {
            $integrationId = $integration->getId();
            $consumerId = $integration->getConsumerId();
            $this->integrationService->delete($integrationId);
            $this->oauthService->deleteConsumer($consumerId);
            return true;
        }
        return false;
    }
}
