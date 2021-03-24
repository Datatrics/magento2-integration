<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;

/**
 * Class ContentAdd
 *
 * Prepare content data
 */
class ContentAdd
{

    /**
     * @var ContentRepository
     */
    private $contentRepository;

    /**
     * @var ContentResource
     */
    private $contentResource;

    /**
     * ContentAdd constructor.
     * @param ContentRepository $contentRepository
     * @param ContentResource $contentResource
     */
    public function __construct(
        ContentRepository $contentRepository,
        ContentResource $contentResource
    ) {
        $this->contentRepository = $contentRepository;
        $this->contentResource = $contentResource;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        return $this->addProducts(null, $output);
    }

    /**
     * @param null|int $storeId
     * @param null|OutputInterface $output
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProducts($storeId = null, $output = null)
    {
        $connection = $this->contentResource->getConnection();
        $stores = [];
        if (!$storeId) {
            $selectStores = $connection->select()->from(
                $this->contentResource->getTable('store'),
                'store_id'
            );
            foreach ($connection->fetchAll($selectStores) as $store) {
                $stores[] = $store['store_id'];
            }
        } else {
            $stores[] = $storeId;
        }
        $selectContent = $connection->select()->from(
            $this->contentResource->getTable('datatrics_content'),
            'content_id'
        );
        if ($storeId) {
            $selectContent->joinLeft(
                ['datatrics_content_store' => $this->contentResource->getTable('datatrics_content_store')],
                'content_id = product_id',
                []
            )->where('datatrics_content_store.store_id = ?', $storeId);
        }

        $select = $connection->select()->from(
            $this->contentResource->getTable('catalog_product_entity'),
            'entity_id'
        )->joinLeft(
            ['super_link' => $this->contentResource->getTable('catalog_product_super_link')],
            'super_link.product_id =' . $this->contentResource->getTable('catalog_product_entity') . '.entity_id',
            [
                'parent_id' => 'GROUP_CONCAT(parent_id)'
            ]
        )->where('entity_id not in (?)', $selectContent)
            ->group('entity_id')->limit(50000);
        $result = $connection->fetchAll($select);

        if ($output) {
            $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, count($result));
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
        $this->contentResource->beginTransaction();
        $pool = 0;
        $data = [];
        foreach ($result as $entity) {
            $count++;
            $pool++;
            $content = $this->contentRepository->create();
            $content->setContentId($entity['entity_id'])
                ->setParentId((string)$entity['parent_id']);
            if ($pool == 1000) {
                $pool = 0;
                if ($output) {
                    $progressBar->setMessage((string)$count, 'product');
                    $progressBar->advance(1000);
                }
            }
            foreach ($stores as $store) {
                $data[] = [
                    $entity['entity_id'],
                    $store,
                    'Queued for Update'
                ];
            }
            $this->contentRepository->save($content);
        }
        if ($output) {
            $progressBar->setMessage((string)$count, 'product');
        }
        if ($data) {
            $connection->insertArray(
                $this->contentResource->getTable('datatrics_content_store'),
                ['product_id', 'store_id', 'status'],
                $data
            );
        }
        $this->contentResource->commit();
        if ($output) {
            $progressBar->finish();
            $output->writeln('');
        }
        return $count;
    }
}
