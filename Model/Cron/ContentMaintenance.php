<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\Serialize\Serializer\Json;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class ContentMaintenance
 *
 * Prepare content data
 */
class ContentMaintenance
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
     * @var ApiAdapter
     */
    private $apiAdapter;

    /**
     * ContentMaintenance constructor.
     * @param ContentResource $contentResource
     * @param ConfigRepository $configRepository
     * @param Json $json
     * @param StoreRepositoryInterface $storeManager
     * @param ContentRepository $contentRepository
     * @param ApiAdapter $apiAdapter
     */
    public function __construct(
        ContentResource $contentResource,
        ConfigRepository $configRepository,
        Json $json,
        StoreRepositoryInterface $storeManager,
        ContentRepository $contentRepository,
        ApiAdapter $apiAdapter
    ) {
        $this->contentResource = $contentResource;
        $this->configRepository = $configRepository;
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->contentRepository = $contentRepository;
        $this->apiAdapter = $apiAdapter;
    }

    /**
     * Collect products which should be scheduled to delete from platform
     */
    private function collectProductsToDelete()
    {
        $connection = $this->contentResource->getConnection();
        $selectMagentoProducts = $connection->select()->from(
            $this->contentResource->getTable('catalog_product_entity'),
            [
                'entity_id'
            ]
        );
        $magentoProductIds = $connection->fetchCol($selectMagentoProducts, 'entity_id');
        $selectDatatricsProducts = $connection->select()->from(
            $this->contentResource->getTable('datatrics_content'),
            [
                'content_id'
            ]
        );
        $datatricsProductIds = $connection->fetchCol($selectDatatricsProducts, 'content_id');
        $toDelete = array_diff($datatricsProductIds, $magentoProductIds);
        $connection->update(
            $this->contentResource->getTable('datatrics_content_store'),
            ['status' => 'Queued for Delete'],
            ['product_id IN (?)' => $toDelete]
        );
        foreach ($toDelete as $itemId) {
            $data = [
                "source" => "Magento 2",
                "type" => "item"
            ];
            $this->apiAdapter->execute(
                ApiAdapter::DELETE_CONTENT,
                $itemId,
                $data
            );
        }
    }

    /**
     * Collect product IDs which should be scheduled to add to platform
     *
     * @return array
     */
    private function collectProductsToAdd()
    {
        $connection = $this->contentResource->getConnection();
        $selectMagentoProducts = $connection->select()->from(
            $this->contentResource->getTable('catalog_product_entity'),
            [
                'entity_id'
            ]
        );
        $magentoProductIds = $connection->fetchCol($selectMagentoProducts, 'entity_id');
        $selectDatatricsProducts = $connection->select()->from(
            $this->contentResource->getTable('datatrics_content_store'),
            [
                'product_id'
            ]
        );
        $datatricsProductIds = $connection->fetchCol($selectDatatricsProducts, 'product_id');
        $toAdd = array_diff($magentoProductIds, $datatricsProductIds);
        return $toAdd;
    }

    /**
     * Schedule products to add to platform
     *
     * @param array $productIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addProducts($productIds)
    {
        $connection = $this->contentResource->getConnection();
        $selectStores = $connection->select()->from(
            $this->contentResource->getTable('store'),
            'store_id'
        );
        $stores = [];
        foreach ($connection->fetchAll($selectStores) as $store) {
            $stores[] = $store['store_id'];
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
        )->where('entity_id in (?)', $productIds)
            ->group('entity_id')->limit(50000);
        $result = $connection->fetchAll($select);
        $this->contentResource->beginTransaction();
        $data = [];
        foreach ($result as $entity) {
            $content = $this->contentRepository->create();
            $content->setContentId($entity['entity_id'])
                ->setParentId((string)$entity['parent_id']);
            foreach ($stores as $store) {
                $data[] = [
                    $entity['entity_id'],
                    $store,
                    'Queued for Update'
                ];
            }
            $this->contentRepository->save($content);
        }
        if ($data) {
            $connection->insertArray(
                $this->contentResource->getTable('datatrics_content_store'),
                ['product_id', 'store_id', 'status'],
                $data
            );
        }
        $this->contentResource->commit();
    }

    /**
     * Schedule products to delete and add
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->configRepository->isEnabled()) {
            return $this;
        }
        $this->collectProductsToDelete();
        $idsToAdd = $this->collectProductsToAdd();
        $this->addProducts($idsToAdd);
        return $this;
    }
}
