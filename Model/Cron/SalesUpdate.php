<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Model\Sales\CollectionFactory as SaleCollectionFactory;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class SalesUpdate
 *
 * Sync orders data with platform
 */
class SalesUpdate
{

    /**
     * @var SaleCollectionFactory
     */
    private $salesCollectionFactory;

    /**
     * @var ApiAdapter
     */
    private $apiAdapter;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * SaleUpdate constructor.
     * @param SaleCollectionFactory $salesCollectionFactory
     * @param ApiAdapter $apiAdapter
     * @param ConfigRepository $configRepository
     * @param Json $json
     */
    public function __construct(
        SaleCollectionFactory $salesCollectionFactory,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        Json $json
    ) {
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->json = $json;
    }

    /**
     * Sync orders with platform
     *
     * @return $this
     */
    public function execute()
    {
        if (!$this->configRepository->isEnabled()) {
            return $this;
        }
        $collection = $this->salesCollectionFactory->create()
            ->addFieldToFilter('status', ['neq' => 'Synced']);
        foreach ($collection as $order) {
            if (!$this->configRepository->getOrderSyncEnabled((int)$order->getStoreId())) {
                continue;
            }
            $response = $this->apiAdapter->execute(
                ApiAdapter::CREATE_CONVERSION,
                null,
                $this->json->serialize($this->prepareData($order))
            );
            if ($response['success']) {
                $order->setStatus('Synced')->save();
            } else {
                $order->setStatus('Error')->save();
                $order->setUpdateAttempts($order->getUpdateAttempts() + 1)->save();
            }
        }
        return $this;
    }

    /**
     * Prepare data to push
     *
     * @param \Datatrics\Connect\Model\Sales\Data $sale
     * @return array
     */
    private function prepareData($sale)
    {
        $conversionData = $sale->getData();
        $conversionData['items'] = $this->json->unserialize($conversionData['items']);
        return [
            "conversionid" => $sale->getOrderId(),
            "projectid" => $this->configRepository->getProjectId(),
            "source" => 'Magento2',
            "conversion" => $conversionData
        ];
    }
}
