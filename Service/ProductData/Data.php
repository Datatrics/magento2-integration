<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Data class
 * Collecting products data according provided IDs and attributes to fetch
 * Return array where keys is product IDs and values is arrays of required data
 */
class Data
{

    /**
     * @var JsonSerializer
     */
    private $json;

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
     * Data constructor.
     * @param JsonSerializer $json
     * @param AttributeCollector\Data\AttributeMapper $attributeMapper
     * @param AttributeCollector\Data\Url $url
     * @param AttributeCollector\Data\Category $category
     * @param AttributeCollector\Data\Stock $stock
     * @param AttributeCollector\Data\Price $price
     */
    public function __construct(
        JsonSerializer $json,
        AttributeCollector\Data\AttributeMapper $attributeMapper,
        AttributeCollector\Data\Url $url,
        AttributeCollector\Data\Category $category,
        AttributeCollector\Data\Stock $stock,
        AttributeCollector\Data\Price $price
    ) {
        $this->json = $json;
        $this->attributeMapper = $attributeMapper;
        $this->url = $url;
        $this->category = $category;
        $this->stock = $stock;
        $this->price = $price;
    }

    /**
     * @param array $entityIds
     * @param array $attributeMap
     * @param array $extraParameters
     * @param int $storeId
     * @return array
     */
    public function execute(array $entityIds, array $attributeMap, array $extraParameters, int $storeId): array
    {
        $result = $this->attributeMapper->execute(
            $entityIds,
            $attributeMap,
            'catalog_product',
            (string)$storeId
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
            (string)$storeId
        );
        foreach ($result as $urlEntityId => $url) {
            $data[$urlEntityId]['url'] = $url;
        }

        $result = $this->category->execute(
            $entityIds,
            (string)$storeId
        );
        foreach ($result as $entityId => $categoryData) {
            $data[$entityId]['category'] = $categoryData;
        }

        if ($extraParameters['advanced']['inventory']) {
            $result = $this->stock->execute(
                $entityIds
            );
            if (!is_array($extraParameters['advanced']['inventory_fields'])) {
                $inventoryFields = explode(',', $extraParameters['advanced']['inventory_fields']);
            } else {
                $inventoryFields = $extraParameters['advanced']['inventory_fields'];
            }

            //adding default inventory data
            $inventoryFields = array_merge(
                $inventoryFields,
                ['msi', 'salable_qty', 'reserved', 'is_in_stock']
            );
            foreach ($result as $entityId => $stockData) {
                $data[$entityId] += array_intersect_key($stockData, array_flip($inventoryFields));
            }
        }
        $result = $this->price->execute(
            $entityIds,
            'max',
            'min'
        );
        foreach ($result as $entityId => $priceData) {
            $data[$entityId] += $priceData;
        }
        return $data;
    }
}
