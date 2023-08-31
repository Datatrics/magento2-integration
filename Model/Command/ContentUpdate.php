<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Api\ProductData\RepositoryInterface as ProductDataRepository;
use Datatrics\Connect\Model\Content\CollectionFactory as ContentCollectionFactory;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ContentUpdate
 *
 * Prepare content data
 */
class ContentUpdate
{

    /**
     * @var ContentResource
     */
    private $contentResource;
    /**
     * @var ApiAdapter
     */
    private $apiAdapter;
    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var bool
     */
    private $isDry = false;
    /**
     * @var ProgressBar|null
     */
    private $progressBar = null;
    /**
     * @var ProductDataRepository
     */
    private $productDataRepository;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var ContentCollectionFactory
     */
    private $contentCollectionFactory;
    /**
     * @var ContentRepository
     */
    private $contentRepository;

    /**
     * @param ContentResource $contentResource
     * @param ApiAdapter $apiAdapter
     * @param ContentRepository $contentRepository
     * @param ContentConfigRepository $contentConfigRepository
     * @param ContentCollectionFactory $contentCollectionFactory
     * @param Json $json
     * @param ProductDataRepository $productDataRepository
     * @param LogRepository $logRepository
     */
    public function __construct(
        ContentResource $contentResource,
        ApiAdapter $apiAdapter,
        ContentRepository $contentRepository,
        ContentConfigRepository $contentConfigRepository,
        ContentCollectionFactory $contentCollectionFactory,
        Json $json,
        ProductDataRepository $productDataRepository,
        LogRepository $logRepository
    ) {
        $this->contentResource = $contentResource;
        $this->apiAdapter = $apiAdapter;
        $this->contentRepository = $contentRepository;
        $this->contentConfigRepository = $contentConfigRepository;
        $this->contentCollectionFactory = $contentCollectionFactory;
        $this->json = $json;
        $this->productDataRepository = $productDataRepository;
        $this->logRepository = $logRepository;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $storeId = (int)$input->getOption('store-id');
        if (!$this->contentConfigRepository->isEnabled($storeId)) {
            $output->writeln('Product synchronisation disabled');
            return 0;
        }

        $this->isDry = (bool)$input->getOption('dry');
        $setProductIds = $input->getArgument('product-id');
        $productIds = $this->getProductIds($storeId, $setProductIds);

        $count = $productIds ? $this->prepareData($productIds, $storeId) : 0;
        $this->initProgressBar($output, $count);
        return 0;
    }

    /**
     * @param int $storeId
     * @param array|null $setProductIds
     * @return array
     */
    public function getProductIds(int $storeId, ?array $setProductIds = null): array
    {
        $connection = $this->contentResource->getConnection();
        $selectProductIds = $connection->select()
            ->from($this->contentResource->getTable('datatrics_content_store'), 'product_id')
            ->where('status != (?)', ContentRepository::STATUS['synced'])
            ->where('store_id = ?', $storeId)
            ->limit($this->contentConfigRepository->getProcessingLimit($storeId))
            ->order('updated_at ASC');

        if ($setProductIds) {
            $selectProductIds->where('product_id in (?)', $setProductIds);
        }

        return $connection->fetchCol($selectProductIds);
    }

    /**
     * Collect products data and push to platform
     *
     * @param array $productIds
     * @param int $storeId
     * @param bool $dryRun
     * @return int
     */
    public function prepareData(array $productIds, int $storeId, bool $dryRun = false): int
    {
        $count = 0;
        $items = ['items' => []];

        $data = $this->productDataRepository->getProductData($storeId, $productIds);
        foreach ($data as $id => $product) {
            $preparedData = [
                "itemid" => $id,
                "source" => $this->contentConfigRepository->getSyncSource((int)$storeId),
                "item" => $product
            ];
            try {
                $this->json->serialize($preparedData);
            } catch (\Exception $e) {
                continue;
            }
            $items['items'][] = $preparedData;
        }

        if ($this->isDry || $dryRun) {
            echo '<pre>'; // phpcs:ignore
            print_r($items); // phpcs:ignore
            return $count;
        }

        if (!$items['items']) {
            $this->updateSkipped($productIds, $storeId);
            return $count;
        }

        $response = $this->apiAdapter->execute(
            ApiAdapter::BULK_CREATE_CONTENT,
            null,
            $this->json->serialize($items)
        );

        if (!$response['success'] || !isset($response['data']['total_elements'])) {
            return $count;
        } else {
            $count += $response['data']['total_elements'];
        }

        $updatedProductIds = [];
        foreach ($response['data']['items'] as $item) {
            $updatedProductIds[] = $item['id'];
        }

        $connection = $this->contentResource->getConnection();
        $connection->update(
            $this->contentResource->getTable('datatrics_content_store'),
            [
                'status' => 'Synced',
                'update_msg' => '',
                'update_attempts' => 0
            ],
            [
                'product_id IN (?)' => $updatedProductIds,
                'store_id = ?' => $storeId
            ]
        );

        $skippedProducts = array_diff($productIds, $updatedProductIds);
        if (!empty($skippedProducts)) {
            $this->updateSkipped($skippedProducts, $storeId);
        }

        if ($this->progressBar) {
            $this->progressBar->setMessage((string)$count, 'content');
            $this->progressBar->advance($count);
        }

        return $count;
    }

    /**
     * @param array $productIds
     * @param int $storeId
     */
    private function updateSkipped(array $productIds, int $storeId)
    {
        $this->logRepository->addDebugLog('Skipped Products', implode(',', $productIds));

        $collection = $this->contentCollectionFactory->create()
            ->addFieldToFilter('product_id', ['in' => $productIds])
            ->addFieldToFilter('store_id', $storeId);

        foreach ($collection as $content) {
            try {
                if ($content->getUpdateAttempts() < 2) {
                    $content->setStatus(ContentRepository::STATUS['skipped'])
                        ->setUpdateAttempts($content->getUpdateAttempts() + 1);
                    $this->contentRepository->save($content);
                } else {
                    $this->contentRepository->delete($content);
                }
            } catch (\Exception $exception) {
                $this->logRepository->addErrorLog('updateSkipped', $exception->getMessage());
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param string|int $size
     */
    private function initProgressBar(OutputInterface $output, $size)
    {
        /* init progress bar */
        $this->progressBar = new \Symfony\Component\Console\Helper\ProgressBar(
            $output,
            $size
        );
        $this->progressBar->setMessage('0', 'content');
        $this->progressBar->setFormat(
            '<info>Content</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%
    <info>⏺ Pushed:    </info>%content%'
        );
    }
}
