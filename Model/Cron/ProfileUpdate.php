<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Api\Config\System\ProfileInterface as ProfileConfigRepository;
use Datatrics\Connect\Model\Profile\CollectionFactory as ProfileCollectionFactory;
use Datatrics\Connect\Model\Profile\Data as ProfileData;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ProfileUpdate
 *
 * Sync customer's data with platform
 */
class ProfileUpdate
{

    /**
     * @var ProfileCollectionFactory
     */
    private $profileCollectionFactory;
    /**
     * @var ApiAdapter
     */
    private $apiAdapter;
    /**
     * @var ProfileConfigRepository
     */
    private $profileConfigRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * ProfileUpdate constructor.
     * @param ProfileCollectionFactory $profileCollectionFactory
     * @param ApiAdapter $apiAdapter
     * @param ProfileConfigRepository $profileConfigRepository
     * @param Json $json
     */
    public function __construct(
        ProfileCollectionFactory $profileCollectionFactory,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        ProfileConfigRepository $profileConfigRepository,
        Json $json
    ) {
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->profileConfigRepository = $profileConfigRepository;
        $this->json = $json;
    }

    /**
     * Push customer's data to platform
     *
     * @return $this
     */
    public function execute()
    {
        if (!$this->configRepository->isEnabled()) {
            return $this;
        }
        $collection = $this->profileCollectionFactory->create()
            ->addFieldToFilter('status', ['neq' => 'Synced']);
        foreach ($collection as $profile) {
            if (!$this->profileConfigRepository->isEnabled((int)$profile->getStoreId())) {
                continue;
            }
            $response = $this->apiAdapter->execute(
                ApiAdapter::CREATE_PROFILE,
                null,
                $this->json->serialize($this->prepareData($profile))
            );
            if ($response['success']) {
                $profile->setStatus('Synced')->save();
            } else {
                $profile->setStatus('Error')->save();
                $profile->setUpdateAttempts($profile->getUpdateAttempts() + 1)->save();
            }
        }
        return $this;
    }

    /**
     * Prepare data to push
     *
     * @param ProfileData $profile
     * @return array
     */
    private function prepareData($profile): array
    {
        $storeId = (int)$profile->getStoreId();
        return [
            "projectid" => $this->profileConfigRepository->getProjectId($storeId),
            "profileid" => $profile->getProfileId(),
            "objecttype" => "profile",
            "source" => $this->profileConfigRepository->getSyncSource($storeId),
            "profile" => $profile->getData()
        ];
    }
}
