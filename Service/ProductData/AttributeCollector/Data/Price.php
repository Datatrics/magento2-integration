<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\ProductData\AttributeCollector\Data;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CatalogPrice;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Service class for price data
 */
class Price
{

    public const REQUIRE = [
        'products',
        'grouped_price_type',
        'bundle_price_type'
    ];

    /**
     * @var CatalogPrice
     */
    private $commonPriceModel;
    /**
     * @var RuleFactory
     */
    private $resourceRuleFactory;
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var TimezoneInterface
     */
    private $localeDate;
    /**
     * @var Collection
     */
    private $collectionFactory;

    private $price = null;
    private $finalPrice = null;
    private $specialPrice = null;
    private $salesPrice = null;
    private $rulePrice = null;
    private $minPrice = null;
    private $maxPrice = null;
    private $totalPrice = null;
    private $websiteId = null;
    private $taxClasses = [];
    private $bundlePriceType = null;
    private $groupedPriceType = null;
    private $products = null;

    /**
     * Price constructor.
     * @param CatalogPrice $commonPriceModel
     * @param RuleFactory $resourceRuleFactory
     * @param CatalogHelper $catalogHelper
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CatalogPrice $commonPriceModel,
        RuleFactory $resourceRuleFactory,
        CatalogHelper $catalogHelper,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CollectionFactory $collectionFactory
    ) {
        $this->commonPriceModel = $commonPriceModel;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->catalogHelper = $catalogHelper;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->collectionFactory = $collectionFactory->create();
    }

    /**
     * @param array $productIds
     *
     * @param string $groupedPriceType options: min, max, total
     * @param string $bundlePriceType optins: min, max, total
     * @param int $storeId
     * @return array
     */
    public function execute(
        array $productIds = [],
        string $groupedPriceType = '',
        string $bundlePriceType = '',
        int $storeId = 0
    ): array {
        $this->websiteId = $this->getWebsiteId((int)$storeId);
        $this->setData('products', $this->getProductData($productIds));
        $this->setData('grouped_price_type', $groupedPriceType);
        $this->setData('bundle_price_type', $bundlePriceType);
        foreach ($this->products as $product) {
            $this->setPrices($product, $this->groupedPriceType, $this->bundlePriceType);
            if (array_key_exists($product->getTaxClassId(), $this->taxClasses)) {
                $percent = $this->taxClasses[$product->getTaxClassId()];
            } else {
                $priceInclTax = $this->processPrice($product, $this->price, true);
                if ($this->price == 0) {
                    $percent = 1;
                } else {
                    $percent = $priceInclTax / $this->price;
                }
                $this->taxClasses[$product->getTaxClassId()] = $percent;
            }
            $result[$product->getId()] = [
                'price' => $percent * $this->price,
                'price_ex' => $percent * $this->price,
                'final_price' => $percent * $this->finalPrice,
                'final_price_ex' => $percent * $this->finalPrice,
                'sales_price' => $percent * $this->salesPrice,
                'min_price' => $percent * $this->minPrice,
                'max_price' => $percent * $this->maxPrice,
                'total_price' => $percent * $this->totalPrice,
                'sales_date_range' => $this->getSpecialPriceDateRang($product),
                'discount_perc' => $this->getDiscountPercentage(),
                'tax' => abs(1 - $percent) * 100
            ];
        }
        return $result ?? [];
    }

    /**
     * @param int $storeId
     * @return int
     */
    private function getWebsiteId($storeId = 0): int
    {
        try {
            return (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        } catch (\Exception $exception) {
            return 0;
        }
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
            case 'products':
                $this->products = $data;
                break;
            case 'grouped_price_type':
                $this->groupedPriceType = $data;
                break;
            case 'bundle_price_type':
                $this->bundlePriceType = $data;
                break;
        }
    }

    /**
     * @param array $productIds
     * @return Collection|AbstractDb
     */
    private function getProductData(array $productIds = [])
    {
        $products = $this->collectionFactory
            ->addFieldToSelect(['price', 'special_price'])
            ->addFieldToFilter('entity_id', ['in' => $productIds]);

        $products->getSelect()->joinLeft(
            ['price_index' => $products->getTable('catalog_product_index_price')],
            join(
                ' AND ',
                [
                    'price_index.entity_id = e.entity_id',
                    'price_index.website_id = ' . $this->websiteId,
                    'price_index.customer_group_id = 0'
                ]
            ),
            ['final_price', 'min_price', 'max_price']
        );

        return $products;
    }

    /**
     * @param Product $product
     * @param string $groupedPriceType
     * @param string $bundlePriceType
     */
    private function setPrices($product, $groupedPriceType, $bundlePriceType): void
    {
        switch ($product->getTypeId()) {
            case 'configurable':
                $this->setConfigurablePrices($product);
                break;
            case 'grouped':
                $this->setGroupedPrices($product, $groupedPriceType);
                break;
            case 'bundle':
                $this->setBundlePrices($product, $bundlePriceType);
                break;
            default:
                $this->setSimplePrices($product);
                break;
        }

        $this->rulePrice = $this->getRulePrice($product);

        if ($this->finalPrice == '0.0000' && $this->minPrice > 0) {
            $this->finalPrice = $this->minPrice;
        }

        if ($this->finalPrice !== null && $this->finalPrice < $this->minPrice) {
            $this->minPrice = $this->finalPrice;
        }

        if ($this->finalPrice == null && $this->specialPrice !== null) {
            $this->finalPrice = $this->specialPrice;
        }

        if ($this->minPrice !== null && $this->price == null) {
            $this->price = $this->minPrice;
        }

        $this->salesPrice = null;
        if ($this->finalPrice !== null && ($this->price > $this->finalPrice)) {
            $this->salesPrice = $this->finalPrice;
        }

        if ($this->finalPrice === null && $this->price !== null) {
            $this->finalPrice = $this->price;
        }
    }

    /**
     * @param Product $product
     */
    private function setConfigurablePrices($product): void
    {
        /**
         * Check if config has a final_price (data catalog_product_index_price)
         * If final_price === null product is not salable (out of stock)
         */
        if ($product->getData('final_price') === null) {
            return;
        }

        $this->price = $product->getData('price');
        $this->finalPrice = $product->getData('final_price');
        $this->specialPrice = $product->getData('special_price');
        $this->minPrice = $product['min_price'] >= 0 ? $product['min_price'] : null;
        $this->maxPrice = $product['max_price'] >= 0 ? $product['max_price'] : null;
    }

    /**
     * @param Product $product
     * @param string $groupedPriceType
     */
    private function setGroupedPrices($product, $groupedPriceType)
    {
        $minPrice = null;
        $maxPrice = null;
        $totalPrice = null;

        /* @var $typeInstance Grouped */
        $typeInstance = $product->getTypeInstance();
        $subProducts = $typeInstance->getAssociatedProducts($product);

        /** @var Product $subProduct */
        foreach ($subProducts as $subProduct) {
            $subProduct->setWebsiteId($this->websiteId);
            if ($subProduct->isSalable()) {
                $price = $this->commonPriceModel->getCatalogPrice($subProduct);
                if ($price < $minPrice || $minPrice === null) {
                    $minPrice = $this->commonPriceModel->getCatalogPrice($subProduct);
                    $product->setTaxClassId($subProduct->getTaxClassId());
                }
                if ($price > $maxPrice || $maxPrice === null) {
                    $maxPrice = $this->commonPriceModel->getCatalogPrice($subProduct);
                    $product->setTaxClassId($subProduct->getTaxClassId());
                }
                if ($subProduct->getQty() > 0) {
                    $totalPrice += $price * $subProduct->getQty();
                } else {
                    $totalPrice += $price;
                }
            }
        }

        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;
        $this->totalPrice = $totalPrice;

        if ($groupedPriceType == 'max') {
            $this->price = $maxPrice;
            $this->finalPrice = $maxPrice;

            return;
        }

        if ($groupedPriceType == 'total') {
            $this->price = $totalPrice;
            $this->finalPrice = $totalPrice;

            return;
        }

        $this->price = $minPrice;
        $this->finalPrice = $minPrice;
    }

    /**
     * @param Product $product
     * @param string $bundlePriceType
     */
    private function setBundlePrices($product, $bundlePriceType): void
    {
        $this->setSimplePrices($product);

        if ($bundlePriceType == 'max') {
            $this->price = $this->maxPrice;
            $this->finalPrice = $this->maxPrice;
        }

        if ($bundlePriceType == 'min') {
            $this->price = $this->minPrice;
            $this->finalPrice = $this->minPrice;
        }
    }

    /**
     * @param Product $product
     */
    private function setSimplePrices($product)
    {
        $this->price = $product->getData('price') !== (float)0 ? $product->getData('price') : null;
        $this->finalPrice = $product->getData('final_price') !== (float)0
            ? $product->getData('final_price') : null;
        $this->specialPrice = $product->getData('special_price') !== (float)0
            ? $product->getData('special_price') : null;
        $this->minPrice = $product['min_price'] >= 0 ? $product['min_price'] : null;
        $this->maxPrice = $product['max_price'] >= 0 ? $product['max_price'] : null;
    }

    /**
     * Get special rule price from product
     *
     * @param Product $product
     *
     * @return float
     */
    private function getRulePrice($product): float
    {
        try {
            $this->rulePrice = $this->resourceRuleFactory->create()->getRulePrice(
                $this->localeDate->scopeDate(),
                $this->websiteId,
                '',
                $product->getId()
            );
        } catch (\Exception $exception) {
            return (float)0;
        }

        if ($this->rulePrice !== null && $this->rulePrice !== false) {
            $this->finalPrice = min($this->finalPrice, $this->rulePrice);
        }

        return (float)$this->rulePrice;
    }

    /**
     * Get product price with or without tax
     *
     * @param Product $product
     * @param float $price inputted product price
     * @param bool $addTax return price include tax flag
     *
     * @return float
     */
    private function processPrice($product, $price, $addTax = true): float
    {
        return (float)$this->catalogHelper->getTaxPrice($product, $price, $addTax);
    }

    /**
     * Get product special price data range
     *
     * @param Product $product
     *
     * @return string
     */
    private function getSpecialPriceDateRang($product)
    {
        if ($this->specialPrice === null) {
            return '';
        }

        if ($this->specialPrice != $this->finalPrice) {
            return '';
        }

        if ($product->getSpecialFromDate() && $product->getSpecialToDate()) {

            /**
             * Todo use Magento date function
             */
            $from = date('Y-m-d', strtotime($product->getSpecialFromDate()));
            $to = date('Y-m-d', strtotime($product->getSpecialToDate()));

            return $from . '/' . $to;
        }
        return '';
    }

    /**
     * Get product discount based on price and sales price
     *
     * @return string
     */
    private function getDiscountPercentage()
    {
        if ($this->price > 0 && $this->salesPrice > 0) {
            $discount = ($this->salesPrice - $this->price) / $this->price;
            $discount = $discount * -100;
            if ($discount > 0) {
                return round($discount, 1) . '%';
            }
        }
        return '0%';
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
            unset($this->products);
            unset($this->groupedPriceType);
            unset($this->bundlePriceType);
        }
        switch ($type) {
            case 'products':
                unset($this->products);
                break;
            case 'grouped_price_type':
                unset($this->groupedPriceType);
                break;
            case 'bundle_price_type':
                unset($this->bundlePriceType);
                break;
        }
    }
}
