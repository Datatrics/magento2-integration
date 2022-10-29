<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\Serialize\Serializer\Json;
use Datatrics\Connect\Model\Content\ResourceModel as CategoryResource;
use Magento\Store\Api\StoreRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Datatrics\Connect\Service\Product\Hub;

/**
 * Class CategoryUpdate
 *
 * Prepare category data
 */
class CategoryUpdate
{

    /**
     * @var CategoryResource
     */
    private $categoryResource;

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
     * @var Hub
     */
    private $collector;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var bool
     */
    private $isDry = false;

    /**
     * @var int
     */
    private $storeId = 1;

    /**
     * CategoryUpdate constructor.
     * @param CategoryResource $categoryResource
     * @param ApiAdapter $apiAdapter
     * @param ConfigRepository $configRepository
     * @param Json $json
     * @param StoreRepositoryInterface $storeManager
     * @param Hub $collector
     */
    public function __construct(
        CategoryResource $categoryResource,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        Json $json,
        StoreRepositoryInterface $storeManager,
        Hub $collector
    ) {
        $this->categoryResource = $categoryResource;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->json = $json;
        $this->storeManager = $storeManager;
        $this->collector = $collector;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->storeId = (int)$input->getOption('store-id');
        $this->isDry = (bool)$input->getOption('dry');
        /* prepare collector to run */
        $this->collector->addData('map', ['name'], 'attributeMapper');
        $connection = $this->categoryResource->getConnection();
        $select = $connection->select()->from(
            ['eav_attribute' => $this->categoryResource->getTable('catalog_category_entity')],
            'entity_id'
        );
        $entityIds = $connection->fetchCol($select);
        $this->collector->addData('entity_ids', $entityIds, 'all');
        $this->collector->addData('entity_type_code', 'catalog_category', 'attributeMapper');
        $this->collector->addData('type', 'category', 'url');
        $data = $this->collector->execute(['url', 'attributeMapper']);
        $this->prepareData($entityIds, $data);
        return 0;
    }

    private function prepareData($entityIds, $data)
    {
        $items = [
            "items" => []
        ];
        $response = [];
        foreach ($entityIds as $entityId) {
            $items["items"][] = [
                "itemid" => $entityId,
                "source" => $this->configRepository->getSyncSource($this->storeId),
                "item" => [
                    "categoryid" => $entityId,
                    "name" => $this->getAttribure($entityId, $data, 'name'),
                    "url" => $this->getUrl($entityId, $data)
                ]
            ];
        }
        if ($this->isDry) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            print_r($items);
        } else {
            $response = $this->apiAdapter->execute(
                ApiAdapter::BULK_CREATE_CATEGORIES,
                null,
                $this->json->serialize($items)
            );
        }
        return $response;
    }

    private function getAttribure($entityId, $data, $attribute)
    {
        if (array_key_exists($this->storeId, $data['attributeMapper'][$attribute][$entityId])) {
            return $data['attributeMapper'][$attribute][$entityId][$this->storeId];
        }
        return $data['attributeMapper'][$attribute][$entityId][0];
    }

    private function getUrl($entityId, $data)
    {
        if (!array_key_exists($entityId, $data['url'])) {
            return '';
        }
        if (array_key_exists($this->storeId, $data['url'][$entityId])) {
            return $data['url'][$entityId][$this->storeId];
        }
        return $data['url'][$entityId][0];
    }
}
