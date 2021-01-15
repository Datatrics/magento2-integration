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
 * Class ContentInvalidate
 *
 * Invalidate content data
 */
class ContentInvalidate
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
     * SalesUpdate constructor.
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
        $connection = $this->contentResource->getConnection();
        if ($storeId = $input->getOption('store-id')) {
            $stores = [$storeId];
        } else {
            $selectStores = $connection->select()->from(
                $connection->getTableName('store'),
                'store_id'
            );
            $stores = [];
            foreach ($connection->fetchAll($selectStores) as $store) {
                $stores[] = $store['store_id'];
            }
        }

        $where = [
            'store_id IN (?)' => $stores
        ];

        if (!empty($input->getArguments()['product-id'])) {
            $where['product_id IN (?)'] = [$input->getArguments()['product-id']];
        }
        return $connection->update(
            $connection->getTableName('datatrics_content_store'),
            ['status' => 'Queued for Update'],
            $where
        );
    }
}
