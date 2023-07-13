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
use Magento\Framework\App\ResourceConnection;
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * SaleUpdate constructor.
     * @param SaleCollectionFactory $salesCollectionFactory
     * @param ApiAdapter $apiAdapter
     * @param ConfigRepository $configRepository
     * @param SalesConfigRepository $salesConfigRepository
     * @param Json $json
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        SaleCollectionFactory $salesCollectionFactory,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        SalesConfigRepository $salesConfigRepository,
        Json $json,
        ResourceConnection $resourceConnection
    ) {
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->salesConfigRepository = $salesConfigRepository;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Sync orders with platform
     *
     * @return $this
     */
    public function execute(): SalesUpdate
    {
        if (!$this->configRepository->isEnabled()) {
            return $this;
        }

        $collection = $this->getCollection();
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

    private function getCollection()
    {
        $collection = $this->salesCollectionFactory->create()
            ->addFieldToFilter(
                'status',
                ['neq' => 'Synced']
            );

        $collection->getSelect()->joinLeft(
            ['sales_order' => $collection->getResource()->getTable('sales_order')],
            'main_table.order_id = sales_order.entity_id',
            [
                'increment_id' => 'sales_order.increment_id',
                'created_at' => 'sales_order.created_at'
            ]
        );

        return $collection;
    }

    /**
     * Prepare data to push
     *
     * @param SalesData $sale
     * @return array
     */
    private function prepareData(SalesData $sale): array
    {
        $storeId = (int)$sale->getStoreId();
        $conversionData = $sale->getData();
        $conversionData['items'] = $this->json->unserialize($conversionData['items']);

        return [
            "conversionid" => $sale->getData('increment_id'),
            "projectid" => $this->salesConfigRepository->getProjectId($storeId),
            "source" => $this->salesConfigRepository->getSyncSource($storeId),
            "conversion" => $conversionData
        ];
    }
}
