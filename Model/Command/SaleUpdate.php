<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Model\Sales\CollectionFactory as SaleCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SaleUpdate
 *
 * Prepare sale data
 */
class SaleUpdate
{

    /**
     * @var SaleCollectionFactory
     */
    private $saleCollectionFactory;

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

    private $isDry = false;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * SaleUpdate constructor.
     * @param SaleCollectionFactory $saleCollectionFactory
     * @param ApiAdapter $apiAdapter
     * @param ConfigRepository $configRepository
     * @param Json $json
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        SaleCollectionFactory $saleCollectionFactory,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        Json $json,
        ResourceConnection $resourceConnection
    ) {
        $this->saleCollectionFactory = $saleCollectionFactory;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->isDry = (bool)$input->getOption('dry');
        $collection = $this->saleCollectionFactory->create()
            ->addFieldToFilter('status', ['neq' => 'Synced']);
        if ($storeId = $input->getOption('store-id')) {
            $collection->addFieldToFilter('store_id', $storeId);
        }

        if ($limit = $input->getOption('limit')) {
            $collection->setPageSize($limit)->setCurPage(1);
        }
        if (!$limit) {
            $limit = $collection->getSize();
        }
        $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, (int)$limit);
        $progressBar->setMessage('0', 'order');
        $progressBar->setFormat(
            '<info>Sales</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%
    <info>⏺ Pushed:    </info>%order%'
        );
        $count = 0;
        $output->writeln('<info>Pushing sales.</info>');
        $progressBar->start();
        $progressBar->display();
        foreach ($collection as $sale) {
            if ($this->isDry) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                print_r($this->prepareData($sale));
                continue;
            }
            $response = $this->apiAdapter->execute(
                ApiAdapter::CREATE_CONVERSION,
                null,
                $this->json->serialize($this->prepareData($sale))
            );
            $count += (int)$response['success'];
            if ($response['success']) {
                $sale->setStatus('Synced')->save();
            } else {
                $sale->setStatus('Error')->save();
                $sale->setUpdateAttempts($sale->getUpdateAttempts() + 1)->save();
            }
            $progressBar->setMessage((string)$count, 'order');
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');
        return 0;
    }

    /**
     * @param \Datatrics\Connect\Model\Sales\Data $sale
     * @return array
     */
    private function prepareData($sale)
    {
        $conversionData = $sale->getData();
        $conversionData['items'] = $this->json->unserialize($conversionData['items']);
        return [
            "conversionid" => $this->getOrderIncrementId($sale->getOrderId()),
            "projectid" => $this->configRepository->getProjectId(),
            "source" => 'Magento2',
            "conversion" => $conversionData
        ];
    }

    /**
     * @param int $orderId
     * @return string
     */
    private function getOrderIncrementId(int $orderId)
    {
        $connection = $this->resourceConnection->getConnection();
        $selectIncrementId = $connection->select()->from(
            $this->resourceConnection->getTableName('sales_order'),
            'increment_id'
        )->where('entity_id = ?', $orderId);
        return (string)$connection->fetchOne($selectIncrementId);
    }
}
