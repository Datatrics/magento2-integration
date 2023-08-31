<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Datatrics\Connect\Model\Config\System\ContentRepository as ConfigContentRepository;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Datatrics\Connect\Model\ProductData\Repository as ProductDataRepository;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ContentAdd
 *
 * Prepare content data
 */
class ContentAdd
{

    /**
     * @var ConfigContentRepository
     */
    private $configRepository;
    /**
     * @var ContentResource
     */
    private $contentResource;
    /**
     * @var ProductDataRepository
     */
    private $productDataRepository;
    /**
     * @var string
     */
    private $entityId;

    /**
     * ContentAdd constructor.
     * @param ConfigContentRepository $configRepository
     * @param ProductDataRepository $productDataRepository
     * @param ContentResource $contentResource
     * @param MetadataPool $metadataPool
     * @throws Exception
     */
    public function __construct(
        ConfigContentRepository $configRepository,
        ProductDataRepository $productDataRepository,
        ContentResource $contentResource,
        MetadataPool $metadataPool
    ) {
        $this->configRepository = $configRepository;
        $this->productDataRepository = $productDataRepository;
        $this->contentResource = $contentResource;
        $this->entityId = $metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }

    /**
     * @param array $storeIds
     * @param OutputInterface $output
     * @return int
     */
    public function run(array $storeIds, OutputInterface $output): int
    {
        $updates = 0;
        foreach ($storeIds as $storeId) {
            $updates += $this->addProducts($storeId, $output);
        }

        return $updates;
    }

    /**
     * @param int|null $storeId
     * @param OutputInterface|null $output
     * @return int
     */
    public function addProducts(int $storeId, ?OutputInterface $output = null): int
    {
        if (!$this->configRepository->isEnabled($storeId)) {
            return 0;
        }

        $productIds = $this->getAllProductIds();
        $productData = $this->productDataRepository->getProductData($storeId, $productIds);
        $currentStoreData = $this->getCurrentStoreData($storeId);
        $this->cleanupTables($storeId, array_keys($productData));

        if ($output) {
            $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, count($productData));
            $progressBar->setMessage('0', 'product');
            $progressBar->setFormat(
                '<info>Content</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%
    <info>⏺ Created:    </info>%product%'
            );
            $output->writeln('<info>Adding content.</info>');
            $progressBar->start();
            $progressBar->display();
        }

        $count = 0;
        $pool = 0;
        $storeData = [];

        foreach ($productData as $productId => $data) {
            $pool++;

            if (!isset($currentStoreData[$productId])) {
                $storeData[] = [
                    (int)$productId,
                    $data['parent_id'] ?? null,
                    (int)$storeId,
                    ContentRepository::STATUS['queued']
                ];
                $count++;
            }

            if ($pool == 1000) {
                $pool = 0;
                if ($output) {
                    /** @phpstan-ignore-next-line */
                    $progressBar->setMessage((string)$count, 'product');
                    /** @phpstan-ignore-next-line */
                    $progressBar->advance(1000);
                }
            }
        }

        if ($output) {
            /** @phpstan-ignore-next-line */
            $progressBar->setMessage((string)$count, 'product');
        }

        $this->updateStoreTable($storeData);

        if ($output) {
            /** @phpstan-ignore-next-line */
            $progressBar->finish();
            $output->writeln('');
        }

        return count($storeData);
    }

    /**
     * @return array
     */
    private function getAllProductIds(): array
    {
        $connection = $this->contentResource->getConnection();
        $table = $this->contentResource->getTable('catalog_product_entity');
        $productIds = $connection->select()
            ->from($table, $this->entityId);

        return array_flip($connection->fetchCol($productIds));
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getCurrentStoreData($storeId): array
    {
        $connection = $this->contentResource->getConnection();
        $table = $this->contentResource->getTable('datatrics_content_store');
        $productIds = $connection->select()
            ->from($table, 'product_id')
            ->where('store_id = ?', $storeId);

        return array_flip($connection->fetchCol($productIds));
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @return void
     */
    private function cleanupTables(int $storeId, array $productIds): void
    {
        $connection = $this->contentResource->getConnection();
        $connection->delete(
            $this->contentResource->getTable('datatrics_content_store'),
            [
                'store_id = ?' => $storeId,
                'product_id NOT IN (?)' => $productIds
            ]
        );
    }

    /**
     * @param array $storeData
     * @return void
     */
    private function updateStoreTable(array $storeData)
    {
        if (empty($storeData)) {
            return;
        }

        $connection = $this->contentResource->getConnection();
        $connection->insertArray(
            $this->contentResource->getTable('datatrics_content_store'),
            ['product_id', 'parent_id', 'store_id', 'status'],
            $storeData
        );
    }
}
