<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Datatrics\Connect\Api\ProductData\RepositoryInterface as ProductDataRepository;
use Magento\Framework\Serialize\Serializer\Json;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Datatrics\Connect\Model\Command\ContentUpdate as CommandContentUpdate;

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
     * @var StoreRepositoryInterface
     */
    private $storeManager;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var ProductDataRepository
     */
    private $productDataRepository;

    /**
     * @var CommandContentUpdate
     */
    private $commandContentUpdate;

    /**
     * ContentUpdate constructor.
     * @param ContentResource $contentResource
     * @param ApiAdapter $apiAdapter
     * @param ContentConfigRepository $contentConfigRepository
     * @param Json $json
     * @param StoreRepositoryInterface $storeManager
     * @param ProductCollection $productCollection
     * @param ProductDataRepository $productDataRepository
     * @param CommandContentUpdate $commandContentUpdate
     */
    public function __construct(
        ContentResource $contentResource,
        ApiAdapter $apiAdapter,
        ContentConfigRepository $contentConfigRepository,
        Json $json,
        StoreRepositoryInterface $storeManager,
        ProductCollection $productCollection,
        ProductDataRepository $productDataRepository,
        CommandContentUpdate $commandContentUpdate
    ) {
        $this->contentResource = $contentResource;
        $this->apiAdapter = $apiAdapter;
        $this->contentConfigRepository = $contentConfigRepository;
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollection;
        $this->productDataRepository = $productDataRepository;
        $this->commandContentUpdate = $commandContentUpdate;
    }

    /**
     * Delete products data
     */
    private function deleteProducts()
    {
        $connection = $this->contentResource->getConnection();
        $select = $connection->select()->from(
            $this->contentResource->getTable('datatrics_content_store'),
            [
                'product_id'
            ]
        )->where('status = ?', 'Queued for Delete');
        $productIds = $connection->fetchCol($select, 'product_id');
        $where = [
            'product_id in (?)' => $productIds
        ];
        $connection->delete($this->contentResource->getTable('datatrics_content_store'), $where);
        $where = [
            'content_id in (?)' => $productIds
        ];
        $connection->delete($this->contentResource->getTable('datatrics_content'), $where);
    }

    /**
     * Delete and update products data
     * @return $this
     */
    public function execute()
    {
        $this->deleteProducts();
        foreach ($this->storeManager->getList() as $store) {
            if (!$this->contentConfigRepository->isEnabled((int)$store->getId()) || $store->getId() == 0) {
                continue;
            }
            if ($store->getIsActive()
                && $this->contentConfigRepository->isEnabled((int)$store->getId())
            ) {
                $this->processStoreData((int)$store->getId());
            }
        }
        return $this;
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
        $productIds = $connection->fetchCol($select, 'product_id');
        $this->commandContentUpdate->prepareData($productIds, $storeId);
    }
}
