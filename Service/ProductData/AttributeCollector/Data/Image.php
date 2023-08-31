<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Service class for image collecting
 */
class Image
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
    private $includeHidden;
    /**
     * @var ?string
     */
    private $mediaUrl = null;
    /**
     * @var string
     */
    private $linkField;

    /**
     * Image constructor.
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
        $this->linkField = $metadataPool->getMetadata(ProductInterface::class)->getLinkField();
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
    public function execute(array $entityIds = [], int $storeId = 0, bool $includeHidden = false): array
    {
        $this->setData('entity_ids', $entityIds);
        $this->setData('store_id', $storeId);
        $this->setData('include_hidden', $includeHidden);
        $imagesData = $this->collectImages();
        $typesData = $this->collectTypes();
        return $this->combineData($imagesData, $typesData);
    }

    /**
     * @param string $type
     * @param mixed $data
     */
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

    /**
     * @return array
     */
    private function collectImages(): array
    {
        $mediaGalleryTable = $this->resource->getTableName('catalog_product_entity_media_gallery');
        $mediaGalleryValueTable = $this->resource->getTableName('catalog_product_entity_media_gallery_value');

        $select = $this->resource->getConnection()
            ->select()->from(
                ['catalog_product_entity_media_gallery' => $mediaGalleryTable],
                'value'
            )->joinLeft(
                ['catalog_product_entity_media_gallery_value' => $mediaGalleryValueTable],
                'catalog_product_entity_media_gallery.value_id = catalog_product_entity_media_gallery_value.value_id',
                ['entity_id' => $this->linkField, 'store_id', 'position']
            )->where(
                'catalog_product_entity_media_gallery_value.store_id IN (?)',
                [0, $this->storeId]
            )->where(
                'catalog_product_entity_media_gallery_value.' . $this->linkField . ' IN (?)',
                $this->entityIds
            );

        if (!$this->includeHidden) {
            $select->where('catalog_product_entity_media_gallery_value.disabled = 0', $this->includeHidden);
        }

        return $this->resource->getConnection()->fetchAll($select);
    }

    /**
     * @return array
     */
    private function collectTypes(): array
    {
        $fields = ['entity_id' => $this->linkField, 'store_id', 'value'];

        $data = [];
        $select = $this->resource->getConnection()
            ->select()->from(
                ['eav_attribute' => $this->resource->getTableName('eav_attribute')],
                ['attribute_code']
            )->joinLeft(
                ['catalog_product_entity_varchar' => $this->resource->getTableName('catalog_product_entity_varchar')],
                'catalog_product_entity_varchar.attribute_id = eav_attribute.attribute_id',
                $fields
            )->where(
                'eav_attribute.frontend_input = ?',
                'media_image'
            )->where(
                'catalog_product_entity_varchar.store_id IN (?)',
                [0, $this->storeId]
            )->where(
                'catalog_product_entity_varchar.' . $this->linkField . ' IN (?)',
                $this->entityIds
            );

        foreach ($this->resource->getConnection()->fetchAll($select) as $item) {
            $data[$item['entity_id']][$item['value']][] = $item['attribute_code'];
        }
        return $data;
    }

    /**
     * @param array $imagesData
     * @param array $typesData
     * @return array
     */
    private function combineData(array $imagesData, array $typesData): array
    {
        $result = [];
        foreach ($imagesData as $imageData) {
            $result[$imageData['entity_id']][$imageData['store_id']][$imageData['position']] = [
                'file' => $this->getMediaUrl('catalog/product' . $imageData['value']),
                'position' => $imageData['position'],
                'types' => (isset($typesData[$imageData['entity_id']][$imageData['value']]))
                    ? $typesData[$imageData['entity_id']][$imageData['value']]
                    : []
            ];
        }
        return $result;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getMediaUrl(string $path): string
    {
        if ($this->mediaUrl == null) {
            try {
                $this->mediaUrl = $this->storeRepository
                    ->getById((int)$this->storeId)
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            } catch (Exception $exception) {
                $this->mediaUrl = '';
            }
        }

        return $this->mediaUrl . $path;
    }

    /**
     * @return string[]
     */
    public function getRequiredParameters(): array
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
}
