<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Exception;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Service class for category path for products
 */
class Category
{
    public const REQUIRE = [
        'entity_ids',
        'store_id'
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var array[]
     */
    private $entityIds;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var int
     */
    private $storeId;
    /**
     * @var string
     */
    private $format;
    /**
     * @var array
     */
    private $categoryNames = [];
    /**
     * @var array
     */
    private $excluded = [];
    /**
     * @var array
     */
    private $exclude = [];
    /**
     * @var string
     */
    private $linkField;
    /**
     * @var string
     */
    private $replaceName = '';
    /**
     * @var array
     */
    private $categoryIds = [];

    /**
     * Category constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreRepositoryInterface $storeRepository
     * @param MetadataPool $metadataPool
     * @throws Exception
     */
    public function __construct(
        ResourceConnection $resource,
        StoreRepositoryInterface $storeRepository,
        MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->storeRepository = $storeRepository;
        $this->linkField = $metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
    }

    /**
     * Get array of products with path of all assigned categories
     *
     * Structure of response
     * [product_id] = [path1, path2, ..., pathN]catalog_category_entity_varchar
     *
     * @param array[] $entityIds array of product IDs
     * @param int $storeId
     * @param string $format
     * @param array $extraParameters
     * @return array[]
     */
    public function execute(
        $entityIds = [],
        int $storeId = 0,
        string $format = 'raw',
        array $extraParameters = []
    ): array {
        if (isset($extraParameters['category']['exclude_attribute'])) {
            $this->setData('exclude', $extraParameters['category']['exclude_attribute']);
        }
        if (isset($extraParameters['category']['replace_attribute'])) {
            $this->setData('replaceName', $extraParameters['category']['replace_attribute']);
        }
        $this->setData('entity_ids', $entityIds);
        $this->setData('store_id', $storeId);
        $this->setData('format', $format);
        $this->collectCategoryNames();
        if (isset($extraParameters['category']['exclude_attribute'])) {
            $this->collectExcluded();
        }
        $data = $this->collectCategories();
        $data = $this->mergeNames($data);
        if (isset($extraParameters['category']['add_url'])) {
            return $this->mergeUrl($data);
        }

        return $data;
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
            case 'store_id':
                $this->storeId = $data;
                break;
            case 'format':
                $this->format = $data;
                break;
            case 'exclude':
                $this->exclude = $data;
                break;
            case 'replaceName':
                $this->replaceName = $data;
                break;
        }
    }

    /**
     * Collect categories name according store IDs
     * @return void
     */
    private function collectCategoryNames(): void
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['eav_attribute' => $this->resource->getTableName('eav_attribute')],
            ['attribute_code']
        )->joinLeft(
            ['catalog_category_entity_varchar' => $this->resource->getTableName('catalog_category_entity_varchar')],
            'catalog_category_entity_varchar.attribute_id = eav_attribute.attribute_id',
            ['entity_id' => $this->linkField, 'value', 'store_id']
        )->where(
            'eav_attribute.attribute_code = ?',
            'name'
        );

        if ($this->replaceName) {
            $select->orWhere('eav_attribute.attribute_code = ?', $this->replaceName);
        }

        $select->where('catalog_category_entity_varchar.store_id IN (?)', [0, $this->storeId]);
        foreach ($connection->fetchAll($select) as $item) {
            if (!$item['value']) {
                continue;
            }
            if (isset($this->categoryNames[$item['entity_id']][$item['store_id']])
                && $item['attribute_code'] == 'name'
                && $this->replaceName
            ) {
                continue;
            }
            $this->categoryNames[$item['entity_id']][$item['store_id']] = $item['value'];
        }
    }

    /**
     * Collect excluded categories
     * @return void
     */
    private function collectExcluded(): void
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['eav_attribute' => $this->resource->getTableName('eav_attribute')],
            ['attribute_code']
        )->joinLeft(
            ['catalog_category_entity_varchar' => $this->resource->getTableName('catalog_category_entity_int')],
            'catalog_category_entity_varchar.attribute_id = eav_attribute.attribute_id',
            ['entity_id' => $this->linkField, 'value', 'store_id']
        )->where(
            'eav_attribute.attribute_code = ?',
            $this->exclude['code']
        )->where(
            'catalog_category_entity_varchar.store_id IN (?)',
            [0, $this->storeId]
        );

        foreach ($connection->fetchAll($select) as $item) {
            if ($item['value'] == $this->exclude['value']) {
                $this->excluded[$item['store_id']][] = $item['entity_id'];
            }
        }
    }

    /**
     * Get path data assigned to products
     *
     * @return array[]
     */
    private function collectCategories(): array
    {
        $path = [];
        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['catalog_category_product' => $this->resource->getTableName('catalog_category_product')],
                'product_id'
            )->joinLeft(
                ['catalog_category_entity' => $this->resource->getTableName('catalog_category_entity')],
                "catalog_category_entity.{$this->linkField} = catalog_category_product.category_id",
                ['path']
            )->where(
                'product_id IN (?)',
                $this->entityIds
            );
        if ($this->excluded) {
            $select->where('catalog_category_entity.' . $this->linkField . ' NOT IN (?)', $this->excluded);
        }
        $result = $this->resource->getConnection()->fetchAll($select);
        foreach ($result as $item) {
            $path[$item['product_id']][] = $item['path'];
        }
        return $path;
    }

    /**
     * @param array $data
     * @return array
     */
    private function mergeNames(array $data): array
    {
        $result = [];
        $realId = 0;
        $rootCategoryId = $this->getRootCategoryId();
        foreach ($data as $entityId => $categoryPaths) {
            $usedPath = [];
            foreach ($categoryPaths as $categoryPath) {
                if (!$categoryPath) {
                    continue;
                }
                $categoryIds = explode('/', $categoryPath);
                $key = array_search($rootCategoryId, $categoryIds);
                if ($key) {
                    $categoryIds = array_slice($categoryIds, $key + 1, count($categoryIds) - $key);
                }
                $level = count($categoryIds);
                if ($level == 0) {
                    continue;
                }
                $categoryNames = [];
                foreach ($categoryIds as &$categoryId) {
                    if (!array_key_exists($categoryId, $this->categoryNames)) {
                        continue;
                    }
                    if (!in_array(end($categoryIds), $this->categoryIds)) {
                        $this->categoryIds[] = end($categoryIds);
                    }
                    $realId = $categoryId;
                    if (!array_key_exists($this->storeId, $this->categoryNames[$categoryId])) {
                        $categoryNames[] = $this->categoryNames[$categoryId][0];
                    } else {
                        $categoryNames[] = $this->categoryNames[$categoryId][$this->storeId];
                    }
                }
                if ($this->format == 'raw') {
                    do {
                        $path = implode(' > ', $categoryNames);
                        if (!in_array($path, $usedPath)) {
                            $result[$entityId][] = [
                                'level' => $level,
                                'path' => $path,
                                'category_id' => $realId
                            ];
                        }
                        $usedPath[] = $path;
                        array_pop($categoryIds);
                        $level--;
                    } while ($level > 0);
                } else {
                    $path = implode(' > ', $categoryNames);
                    $result[$entityId][] = $path;
                }
            }
        }
        return $result;
    }

    /**
     * @return int|null
     */
    private function getRootCategoryId(): ?int
    {
        try {
            return (int)$this->storeRepository->getById($this->storeId)->getRootCategoryId();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function mergeUrl(array $data): array
    {
        try {
            $baseUrl = $this->storeRepository->getById((int)$this->storeId)->getBaseUrl();
        } catch (Exception $exception) {
            $baseUrl = '';
        }

        $select = $this->resource->getConnection()
            ->select()
            ->from(
                ['catalog_category_entity' => $this->resource->getTableName('catalog_category_entity')],
                [$this->linkField]
            )->join(
                ['url_rewrite' => $this->resource->getTableName('url_rewrite')],
                'catalog_category_entity.entity_id = url_rewrite.entity_id',
            )->where(
                "catalog_category_entity.{$this->linkField} in (?)",
                $this->categoryIds,
            )->where(
                'entity_type = ?',
                'category'
            );

        $urls = $this->resource->getConnection()->fetchAll($select);
        foreach ($data as &$datum) {
            foreach ($datum as &$item) {
                $key = array_search($item['category_id'], array_column($urls, 'entity_id'));
                if ($key !== false && isset($urls[$key]['request_path'])) {
                    $item['url'] = $baseUrl . $urls[$key]['request_path'];
                }
            }
        }

        return $data;
    }

    /**
     * Return Required Parameters
     *
     * @return string[]
     */
    public function getRequiredParameters(): array
    {
        return self::REQUIRE;
    }

    /**
     * @param string $type
     */
    public function resetData(string $type = 'all'): void
    {
        if ($type == 'all') {
            unset($this->entityIds);
            unset($this->storeId);
        }
        switch ($type) {
            case 'entity_ids':
                unset($this->entityIds);
                break;
            case 'store_id':
                unset($this->storeId);
                break;
            case 'format':
                unset($this->format);
                break;
            case 'exclude':
                unset($this->exclude);
                break;
        }
    }
}
