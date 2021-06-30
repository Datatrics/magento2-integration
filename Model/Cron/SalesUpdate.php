<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Api\Config\System\SalesInterface as SalesConfigRepository;
use Datatrics\Connect\Model\Sales\CollectionFactory as SaleCollectionFactory;
use Datatrics\Connect\Model\Sales\Data as SalesData;
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
     * @var SalesConfigRepository
     */
    private $salesConfigRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * SaleUpdate constructor.
     * @param SaleCollectionFactory $salesCollectionFactory
     * @param ApiAdapter $apiAdapter
     * @param SalesConfigRepository $salesConfigRepository
     * @param Json $json
     */
    public function __construct(
        SaleCollectionFactory $salesCollectionFactory,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        SalesConfigRepository $salesConfigRepository,
        Json $json
    ) {
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->salesConfigRepository = $salesConfigRepository;
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
            if (!$this->salesConfigRepository->isEnabled((int)$order->getStoreId())) {
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
     * @param SalesData $sale
     * @return array
     */
    private function prepareData($sale): array
    {
        $conversionData = $sale->getData();
        $conversionData['items'] = $this->json->unserialize($conversionData['items']);
        return [
            "conversionid" => $sale->getOrderId(),
            "projectid" => $this->salesConfigRepository->getProjectId((int)$sale->getStoreId()),
            "source" => $this->salesConfigRepository->getSyncSource((int)$sale->getStoreId()),
            "conversion" => $conversionData
        ];
    }
}
