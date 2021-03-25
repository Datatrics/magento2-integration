<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Service class for URL data
 * Allow to get URLs for categories, pages and products
 */
class Url
{

    const REQUIRE = [
        'entity_ids',
        'store_id',
        'type'
    ];

    /**
     * URL pattern for entities
     */
    const URL_PATTERN = '%s%s';

    /**
     * URL pattern for no-rewrite items
     */
    const URL_PATTERN_EXTRA = [
        'product' => '%scatalog/product/view/id/%s',
        'cms-page' => '%scms/page/view/page_id/%s',
        'category' => '%scatalog/category/view/id/%s'
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var array
     */
    private $entityIds;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $storeId;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * Price constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ResourceConnection $resource,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->resource = $resource;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get URL data
     *
     * Structure of response
     * [product_id][store_id] = url
     *
     * @param array[] $entityIds array with IDs or products, categories or pages
     * @param string $type category, cms-page or product
     * @param int $storeId ID of store to fetch data
     *
     * @return array[]
     */
    public function execute(array $entityIds = [], string $type = '', int $storeId = 0): array
    {
        $this->setData('entity_ids', $entityIds);
        $this->setData('store_id', $storeId);
        $this->setData('entity_ids', $entityIds);
        $this->setData('type', $type);
        return $this->collectUrl();
    }

    /**
     * @param string $type
     * @param mixed $data
     */
    public function setData($type, $data): void
    {
        if (!$data) {
            return;
        }
        switch ($type) {
            case 'entity_ids':
                $this->entityIds = $data;
                break;
            case 'type':
                $this->type = $data;
                break;
            case 'store_id':
                $this->storeId = $data;
                break;
        }
    }

    /**
     * Collect URL data for entities
     *
     * @return array
     */
    private function collectUrl(): array
    {
        $result = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName('url_rewrite'),
                ['entity_id', 'request_path']
            )->where('entity_id IN (?)', $this->entityIds)
            ->where('redirect_type = ?', 0)
            ->where('metadata IS NULL')
            ->where('store_id = ?', $this->storeId)
            ->where('entity_type = ?', $this->type);
        $values = $this->resource->getConnection()->fetchAll($select);
        $storeUrl = $this->getStoreUrl();
        foreach ($values as $value) {
            $result[$value['entity_id']] = sprintf(
                self::URL_PATTERN,
                $storeUrl,
                $value['request_path']
            );
        }
        foreach ($this->entityIds as $entityId) {
            if (!array_key_exists($entityId, $result)) {
                $result[$entityId] = sprintf(
                    self::URL_PATTERN_EXTRA[$this->type],
                    $storeUrl,
                    $entityId
                );
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    private function getStoreUrl(): string
    {
        try {
            return $this->storeRepository->getById($this->storeId)->getBaseUrl();
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * @return string[]
     */
    public function getRequiredParameters()
    {
        return self::REQUIRE;
    }

    /**
     * @param string $type
     */
    public function resetData($type = 'all')
    {
        if ($type == 'all') {
            unset($this->entityIds);
            unset($this->type);
            unset($this->storeId);
        }
        switch ($type) {
            case 'entity_ids':
                unset($this->entityIds);
                break;
            case 'type':
                unset($this->type);
                break;
            case 'store_id':
                unset($this->storeId);
                break;
        }
    }
}
