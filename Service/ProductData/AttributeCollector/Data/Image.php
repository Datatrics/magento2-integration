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
 * Service class for image collecting
 */
class Image
{

    const REQIURE = [
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
     * @var string
     */
    private $storeId;

    /**
     * @var string
     */
    private $includeHidden;

    /**
     * Image constructor.
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
     * Get array of all product images with types
     *
     * Structure of response
     * [product_id] = [path1, path2, ..., pathN]
     *
     * @param array[] $entityIds array of product IDs
     * @param int $storeId store ID
     * @param bool $includeHidden collect hidden images
     * @return array[]
     */
    public function execute(array $entityIds, int $storeId, bool $includeHidden = false): array
    {
        $this->setData('entity_ids', $entityIds);
        $this->setData('store_id', $storeId);
        $this->setData('include_hidden', $includeHidden);
        $imagesData = $this->collectImages();
        $typesData = $this->collectTypes();
        return $this->combineData($imagesData, $typesData);
    }

    private function combineData($imagesData, $typesData)
    {
        $result = [];
        $storeUrl = $this->storeRepository->getById((int)$this->storeId)
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        foreach ($imagesData as $imageData) {
            $result[$imageData['entity_id']][$imageData['store_id']][$imageData['position']] = [
                'file' => sprintf('%scatalog/product%s', $storeUrl, $imageData['value']),
                'position' => $imageData['position'],
                'types' => (isset($typesData[$imageData['entity_id']][$imageData['value']]))
                    ? $typesData[$imageData['entity_id']][$imageData['value']]
                    : []
            ];
        }
        return $result;
    }

    private function collectImages()
    {
        $data = [];
        $mediaGalleryTable = $this->resource->getTableName('catalog_product_entity_media_gallery');
        $mediaGalleryValueTable = $this->resource->getTableName('catalog_product_entity_media_gallery_value');
        $select = $this->resource->getConnection()
            ->select()->from(
                ['catalog_product_entity_media_gallery' => $mediaGalleryTable],
                'value'
            )->joinLeft(
                ['catalog_product_entity_media_gallery_value' => $mediaGalleryValueTable],
                'catalog_product_entity_media_gallery.value_id = catalog_product_entity_media_gallery_value.value_id',
                ['entity_id', 'store_id', 'position']
            )->where('catalog_product_entity_media_gallery_value.entity_id IN (?)', $this->entityIds)
            ->where('catalog_product_entity_media_gallery_value.store_id IN (?)', [0, $this->storeId]);
        if (!$this->includeHidden) {
            $select->where('catalog_product_entity_media_gallery_value.disabled = 0', $this->includeHidden);
        }
        return $this->resource->getConnection()->fetchAll($select);
    }

    private function collectTypes()
    {
        $data = [];
        $select = $this->resource->getConnection()
        ->select()->from(
            ['eav_attribute' => $this->resource->getTableName('eav_attribute')],
            ['attribute_code']
        )->joinLeft(
            ['catalog_product_entity_varchar' => $this->resource->getTableName('catalog_product_entity_varchar')],
            'catalog_product_entity_varchar.attribute_id = eav_attribute.attribute_id',
            ['entity_id', 'store_id', 'value']
        )->where('eav_attribute.frontend_input = ?', 'media_image')
        ->where('catalog_product_entity_varchar.entity_id IN (?)', $this->entityIds)
        ->where('catalog_product_entity_varchar.store_id IN (?)', [0, $this->storeId]);
        foreach ($this->resource->getConnection()->fetchAll($select) as $item) {
            $data[$item['entity_id']][$item['value']][] = $item['attribute_code'];
        }
        return $data;
    }

    public function getRequiredParameters()
    {
        return self::REQIURE;
    }

    public function resetData($type = 'all')
    {
        if ($type == 'all') {
            unset($this->entityIds);
            unset($this->storeId);
            unset($this->includeHidden);
        }
        switch ($type) {
            case 'entity_ids':
                unset($this->entityIds);
                break;
            case 'store_id':
                unset($this->storeId);
                break;
            case 'include_hidden':
                unset($this->includeHidden);
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
            case 'store_id':
                $this->storeId = $data;
                break;
            case 'include_hidden':
                $this->includeHidden = $data;
                break;
        }
    }
}
