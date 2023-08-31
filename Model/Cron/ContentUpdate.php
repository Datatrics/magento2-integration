<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Datatrics\Connect\Model\Command\ContentUpdate as CommandContentUpdate;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class ContentUpdate
 *
 * Add product data to platform
 */
class ContentUpdate
{

    /**
     * @var ContentResource
     */
    private $contentResource;
    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeManager;
    /**
     * @var CommandContentUpdate
     */
    private $commandContentUpdate;

    /**
     * ContentUpdate constructor.
     * @param ContentResource $contentResource
     * @param ContentConfigRepository $contentConfigRepository
     * @param StoreRepositoryInterface $storeManager
     * @param CommandContentUpdate $commandContentUpdate
     */
    public function __construct(
        ContentResource $contentResource,
        ContentConfigRepository $contentConfigRepository,
        StoreRepositoryInterface $storeManager,
        CommandContentUpdate $commandContentUpdate
    ) {
        $this->contentResource = $contentResource;
        $this->contentConfigRepository = $contentConfigRepository;
        $this->storeManager = $storeManager;
        $this->commandContentUpdate = $commandContentUpdate;
    }

    /**
     * Delete and update products data
     * @return $this
     */
    public function execute(): ContentUpdate
    {
        $this->deleteProducts();

        foreach ($this->storeManager->getList() as $store) {
            if (!$this->contentConfigRepository->isEnabled((int)$store->getId()) || $store->getId() == 0) {
                continue;
            }
            if ($store->getIsActive() && $this->contentConfigRepository->isEnabled((int)$store->getId())) {
                $this->processStoreData((int)$store->getId());
            }
        }
        return $this;
    }

    /**
     * Delete products data
     */
    private function deleteProducts()
    {
        $connection = $this->contentResource->getConnection();
        $select = $connection->select()->from(
            $this->contentResource->getTable('datatrics_content_store'),
            ['product_id']
        )->where('status = ?', 'Queued for Delete');
        $productIds = $connection->fetchCol($select);

        $connection->delete(
            $this->contentResource->getTable('datatrics_content_store'),
            ['product_id in (?)' => $productIds]
        );
    }

    /**
     * @param int $storeId
     */
    private function processStoreData(int $storeId)
    {
        $connection = $this->contentResource->getConnection();
        $select = $connection->select()->from(
            $this->contentResource->getTable('datatrics_content_store'),
            [
                'product_id',
                'update_attempts'
            ]
        )->where('store_id = ?', $storeId);
        $select->where('status <> ?', 'Synced');
        if (!$connection->fetchOne($select)) {
            return;
        }
        $productIds = $connection->fetchCol($select);
        $this->commandContentUpdate->prepareData($productIds, $storeId);
    }
}
