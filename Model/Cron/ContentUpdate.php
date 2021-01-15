<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Api\ProductData\RepositoryInterface as ProductDataRepository;
use Magento\Framework\Serialize\Serializer\Json;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

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
     * @var ConfigRepository
     */
    private $configRepository;

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
     * ContentUpdate constructor.
     * @param ContentResource $contentResource
     * @param ApiAdapter $apiAdapter
     * @param ConfigRepository $configRepository
     * @param Json $json
     * @param StoreRepositoryInterface $storeManager
     * @param ProductCollection $productCollection
     * @param ProductDataRepository $productDataRepository
     */
    public function __construct(
        ContentResource $contentResource,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        Json $json,
        StoreRepositoryInterface $storeManager,
        ProductCollection $productCollection,
        ProductDataRepository $productDataRepository
    ) {
        $this->contentResource = $contentResource;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollection;
        $this->productDataRepository = $productDataRepository;
    }

    /**
     * Delete products data
     */
    private function deleteProducts()
    {
        $connection = $this->contentResource->getConnection();
        $select = $connection->select()->from(
            $connection->getTableName('datatrics_content_store'),
            [
                'product_id'
            ]
        )->where('status = ?', 'Queued for Delete');
        $productIds = $connection->fetchCol($select, 'product_id');
        $where = [
            'product_id in (?)' => $productIds
        ];
        $connection->delete($connection->getTableName('datatrics_content_store'), $where);
        $where = [
            'content_id in (?)' => $productIds
        ];
        $connection->delete($connection->getTableName('datatrics_content'), $where);
    }

    /**
     * Delete and update products data
     * @return $this|int
     */
    public function execute()
    {
        if (!$this->configRepository->isEnabled()
            || !$this->configRepository->isProductSyncEnabled()) {
            return $this;
        }
        $this->deleteProducts();
        $connection = $this->contentResource->getConnection();
        $select = $connection->select()->from(
            $connection->getTableName('datatrics_content_store'),
            [
                'product_id',
                'update_attempts'
            ]
        )->where('store_id = ?', 1);
        $select->where('status <> ?', 'Synced');
        if (!$connection->fetchOne($select)) {
            return 0;
        }
        $productIds = $connection->fetchCol($select, 'product_id');
        $attempts = $connection->fetchPairs($select);
        $this->prepareData($productIds, 1, $attempts);
        return 0;
    }

    /**
     * Collect products data and push to platform
     *
     * @param array $productIds
     * @param string|int $storeId
     * @param string|int $attempts
     * @return int
     */
    private function prepareData($productIds, $storeId, $attempts)
    {
        $connection = $this->contentResource->getConnection();
        $count = 0;
        $items = [
            'items' => []
        ];
        $data = $this->productDataRepository->getProductData($storeId, $productIds);
        foreach ($data as $id => $product) {
            $preparedData = [
                "itemid" => $id,
                "source" => "Magento 2",
                "item" => $product
            ];
            try {
                $serializedData = $this->json->serialize($preparedData);
            } catch (\Exception $e) {
                continue;
            }
            $items['items'][] = $preparedData;
        }
        if (!$items['items']) {
            return $count;
        }
        //bulk update
        $response = $this->apiAdapter->execute(
            ApiAdapter::BULK_CREATE_CONTENT,
            null,
            $this->json->serialize($items)
        );
        if ($response['success']) {
            $count += $response['data']['total_elements'];
        }
        $productIds = [];
        foreach ($response['data']['items'] as $item) {
            $productIds[] = $item['id'];
        }
        $where = [
            'product_id IN (?)' => $productIds,
            'store_id = ?' => $storeId
        ];
        if ($response['success']) {
            $connection->update(
                $connection->getTableName('datatrics_content_store'),
                [
                    'status' => 'Synced',
                    'update_msg' => '',
                    'update_attempts' => 0
                ],
                $where
            );
        } else {
            $connection->update(
                $connection->getTableName('datatrics_content_store'),
                [
                    'status' => 'Error',
                    'update_msg' => ''
                ],
                $where
            );
        }
        return $count;
    }
}
