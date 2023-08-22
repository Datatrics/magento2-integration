<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData;

use Exception;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Data class
 * Collecting products data according provided IDs and attributes to fetch
 * Return array where keys is product IDs and values is arrays of required data
 */
class Data
{

    /**
     * @var AttributeCollector\Data\AttributeMapper
     */
    private $attributeMapper;
    /**
     * @var AttributeCollector\Data\Url
     */
    private $url;
    /**
     * @var AttributeCollector\Data\Category
     */
    private $category;
    /**
     * @var AttributeCollector\Data\Stock
     */
    private $stock;
    /**
     * @var AttributeCollector\Data\Price
     */
    private $price;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var string
     */
    private $linkField;

    /**
     * @param AttributeCollector\Data\AttributeMapper $attributeMapper
     * @param AttributeCollector\Data\Url $url
     * @param AttributeCollector\Data\Category $category
     * @param AttributeCollector\Data\Stock $stock
     * @param AttributeCollector\Data\Price $price
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @throws Exception
     */
    public function __construct(
        AttributeCollector\Data\AttributeMapper $attributeMapper,
        AttributeCollector\Data\Url $url,
        AttributeCollector\Data\Category $category,
        AttributeCollector\Data\Stock $stock,
        AttributeCollector\Data\Price $price,
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->attributeMapper = $attributeMapper;
        $this->url = $url;
        $this->category = $category;
        $this->stock = $stock;
        $this->price = $price;
        $this->resourceConnection = $resourceConnection;
        $this->linkField = $metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
    }

    /**
     * @param array $entityIds
     * @param array $attributeMap
     * @param array $extraParameters
     * @param int $storeId
     * @return array
     */
    public function execute(array $entityIds, array $attributeMap, array $extraParameters, int $storeId = 0): array
    {
        $rowIds = $this->getRowsIds($entityIds);
        $productIds = array_flip($rowIds);

        $result = $this->attributeMapper->execute(
            $entityIds,
            $attributeMap,
            'catalog_product',
            $storeId
        );

        $data = [];
        foreach ($attributeMap as $targetCode => $attributeCode) {
            if (!isset($result[$attributeCode])) {
                continue;
            }
            foreach ($result[$attributeCode] as $entityId => $value) {
                $data[$entityId][$targetCode] = $value;
            }
        }

        $result = $this->url->execute(
            $entityIds,
            'product',
            $storeId
        );

        foreach ($result as $urlEntityId => $url) {
            $data[$urlEntityId]['url'] = $url;
        }

        $result = $this->category->execute(
            $productIds,
            $storeId,
            'raw',
            $extraParameters
        );

        foreach ($result as $productId => $categoryData) {
            $data[$rowIds[$productId]]['category'] = $categoryData;
        }

        if ($extraParameters['stock']['inventory']) {
            $result = $this->stock->execute($productIds);
            $inventoryFields = array_merge(
                $extraParameters['stock']['inventory_fields'],
                ['qty', 'msi', 'salable_qty', 'reserved', 'is_in_stock']
            );

            foreach ($result as $productId => $stockData) {
                $data[$rowIds[$productId]] += array_intersect_key($stockData, array_flip($inventoryFields));
            }
        }

        $result = $this->price->execute(
            $productIds,
            $extraParameters['behaviour']['grouped']['price_logic'] ?? 'max',
            $extraParameters['behaviour']['bundle']['price_logic'] ?? 'min',
            $storeId
        );

        foreach ($result as $productId => $priceData) {
            $data[$rowIds[$productId]] += $priceData;
        }

        return $data;
    }

    /**
     * @param array $entityIds
     * @return int[]|string[]
     */
    private function getRowsIds(array $entityIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('catalog_product_entity');
        $select = $connection->select()
            ->from($table, ['entity_id', $this->linkField])
            ->where("{$this->linkField} IN (?)", $entityIds);

        return $connection->fetchPairs($select);
    }
}
