<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Product\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Service class for URL data
 * Allow to get URLs for categories, pages and products
 */
class Url
{

    public const REQIURE = [
        'entity_ids',
        'type'
    ];

    /**
     * URL pattern for entities
     */
    public const URL_PATTERN = '%s%s';

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
     *
     * @return array[]
     */
    public function execute(array $entityIds = [], string $type = ''): array
    {
        $this->setData('entity_ids', $entityIds);
        $this->setData('type', $type);
        return $this->collectUrl();
    }

    public function getRequiredParameters()
    {
        return self::REQIURE;
    }

    public function resetData($type = 'all')
    {
        if ($type == 'all') {
            unset($this->entityIds);
            unset($this->type);
        }
        switch ($type) {
            case 'entity_ids':
                unset($this->entityIds);
                break;
            case 'type':
                unset($this->type);
                break;
        }
    }

    public function setData($type, $data)
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
        }
    }

    /**
     * Collect URL data for entities
     *
     * @return array[]
     */
    private function collectUrl(): array
    {
        $result = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                $this->resource->getTableName('url_rewrite'),
                [
                    'entity_id',
                    'request_path',
                    'store_id'
                ]
            )->where('entity_id IN (?)', $this->entityIds)
            ->where('redirect_type = ?', 0)
            ->where('metadata IS NULL')
            ->where('entity_type = ?', $this->type);
        $values = $this->resource->getConnection()->fetchAll($select);
        $storeUrl = $this->collectStoreUrl();
        foreach ($values as $value) {
            $result[$value['entity_id']][$value['store_id']] = sprintf(
                self::URL_PATTERN,
                $storeUrl[$value['store_id']],
                $value['request_path']
            );
        }
        return $result;
    }

    /**
     * Collect stores URL
     */
    private function collectStoreUrl()
    {
        $storeUrl = [];
        foreach ($this->storeRepository->getList() as $store) {
            $url = $store->getBaseUrl();
            if (substr($url, -1) != '/') {
                $url .= '/';
            }
            $storeUrl[$store->getId()] = $url;
        }
        return $storeUrl;
    }
}
