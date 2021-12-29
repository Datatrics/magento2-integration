<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Webapi;

use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Datatrics\Connect\Api\Config\System\ProfileInterface as ProfileConfigRepository;
use Datatrics\Connect\Api\Config\System\SalesInterface as SalesConfigRepository;
use Datatrics\Connect\Api\Config\System\TrackingInterface as TrackingConfigRepository;
use Datatrics\Connect\Api\Webapi\RepositoryInterface;
use Datatrics\Connect\Model\Content\CollectionFactory as ContentCollectionFactory;
use Datatrics\Connect\Model\Profile\CollectionFactory as ProfileCollectionFactory;
use Datatrics\Connect\Model\Sales\CollectionFactory as ConversionCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Datatrics webapi repository class
 */
class Repository implements RepositoryInterface
{

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;

    /**
     * @var ProfileConfigRepository
     */
    private $profileConfigRepository;

    /**
     * @var SalesConfigRepository
     */
    private $salesConfigRepository;

    /**
     * @var TrackingConfigRepository
     */
    private $trackingConfigRepository;

    /**
     * @var ContentCollectionFactory
     */
    private $contentCollectionFactory;

    /**
     * @var ConversionCollectionFactory
     */
    private $conversionCollectionFactory;

    /**
     * @var ProfileCollectionFactory
     */
    private $profileCollectionFactory;

    /**
     * Repository constructor.
     * @param Json $json
     * @param ConfigRepository $configRepository
     * @param ContentConfigRepository $contentConfigRepository
     * @param ProfileConfigRepository $profileConfigRepository
     * @param SalesConfigRepository $salesConfigRepository
     * @param TrackingConfigRepository $trackingConfigRepository
     * @param ContentCollectionFactory $contentCollectionFactory
     * @param ConversionCollectionFactory $conversionCollectionFactory
     * @param ProfileCollectionFactory $profileCollectionFactory
     */
    public function __construct(
        Json $json,
        ConfigRepository $configRepository,
        ContentConfigRepository $contentConfigRepository,
        ProfileConfigRepository $profileConfigRepository,
        SalesConfigRepository $salesConfigRepository,
        TrackingConfigRepository $trackingConfigRepository,
        ContentCollectionFactory $contentCollectionFactory,
        ConversionCollectionFactory $conversionCollectionFactory,
        ProfileCollectionFactory $profileCollectionFactory
    ) {
        $this->json = $json;
        $this->configRepository = $configRepository;
        $this->contentConfigRepository = $contentConfigRepository;
        $this->profileConfigRepository = $profileConfigRepository;
        $this->salesConfigRepository = $salesConfigRepository;
        $this->trackingConfigRepository = $trackingConfigRepository;
        $this->contentCollectionFactory = $contentCollectionFactory;
        $this->conversionCollectionFactory = $conversionCollectionFactory;
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getInformation(): string
    {
        $data = [
            'Magento_version' => $this->configRepository->getMagentoVersion(),
            'Plugin_version' => $this->configRepository->getExtensionVersion(),
            'Profiles' => ($this->profileConfigRepository->isEnabled()) ? 'enabled' : 'disabled',
            'Profiles_timestamp' => $this->getLastProfileSync(),
            'Content' => ($this->contentConfigRepository->isEnabled()) ? 'enabled' : 'disabled',
            'Content_timestamp' => $this->getLastContentSync(),
            'Conversions' => ($this->salesConfigRepository->isEnabled()) ? 'enabled' : 'disabled',
            'Conversions_timestamp' => $this->getLastConversionSync(),
            'Pixel' => ($this->trackingConfigRepository->isEnabled()) ? 'enabled' : 'disabled'
        ];
        return $this->json->serialize($data);
    }

    private function getLastProfileSync(): string
    {
        $collection = $this->profileCollectionFactory->create()
            ->addFieldToFilter('status', 'Synced')
            ->setOrder('updated_at')
            ->setPageSize(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getUpdatedAt();
        }
        return '';
    }

    private function getLastContentSync(): string
    {
        $collection = $this->contentCollectionFactory->create()
            ->setOrder('updated_at')
            ->setPageSize(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getUpdatedAt();
        }
        return '';
    }

    private function getLastConversionSync(): string
    {
        $collection = $this->conversionCollectionFactory->create()
            ->addFieldToFilter('status', 'Synced')
            ->setOrder('updated_at')
            ->setPageSize(1);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getUpdatedAt();
        }
        return '';
    }
}
