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
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Service class for price data
 */
class Price
{

    /**
     *
     */
    const REQIURE = [
        'products',
        'grouped_price_type',
        'bundle_price_type'
    ];

    private $price = null;
    private $finalPrice = null;
    private $specialPrice = null;
    private $salesPrice = null;
    private $rulePrice = null;
    private $minPrice = null;

    /**
     * @var float|string|null
     */
    private $maxPrice = null;

    /**
     * @var float|string|null
     */
    private $totalPrice = null;

    /**
     * @var int|string|null
     */
    private $storeId = null;

    /**
     * @var int|string|null
     */
    private $websiteId = null;

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
     * @var array
     */
    private $taxClasses  = [];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $bundlePriceType;

    /**
     * @var string
     */
    private $groupedPriceType;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection;
     */
    private $products;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $collectionFactory;

    /**
     * Price constructor.
     *
     * @param CatalogPrice          $commonPriceModel
     * @param RuleFactory           $resourceRuleFactory
     * @param CatalogHelper         $catalogHelper
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface     $localeDate
     */
    public function __construct(
        CatalogPrice $commonPriceModel,
        RuleFactory $resourceRuleFactory,
        CatalogHelper $catalogHelper,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CollectionFactory $collectionFactory,
        Logger $logger
    ) {
        $this->commonPriceModel = $commonPriceModel;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->catalogHelper = $catalogHelper;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->collectionFactory = $collectionFactory->create();
        $this->logger = $logger;
    }

    /**
     * @param array $productIds
     *
     * @param string  $groupedPriceType options: min, max, total
     * @param string  $bundlePriceType optins: min, max, total
     *
     * @return array
     */
    public function execute($productIds = [], $groupedPriceType = '', $bundlePriceType = ''): array
    {
        $result = [];
        /** @phpstan-ignore-next-line */
        $products = $this->collectionFactory->addFieldToFilter('entity_id', ['in' => $productIds])->addPriceData();
        $this->setData('products', $products);
        $this->setData('grouped_price_type', $groupedPriceType);
        $this->setData('bundle_price_type', $bundlePriceType);
        foreach ($this->products as $product) {
            $this->storeId = $product->getStoreId();
            $this->websiteId = $this->getWebsiteId();
            $this->setPrices($product, $this->groupedPriceType, $this->bundlePriceType);
            if (array_key_exists($product->getTaxClassId(), $this->taxClasses)) {
                $percent = $this->taxClasses[$product->getTaxClassId()];
            } else {
                $priceInclTax = $this->processPrice($product, $this->price, true);
                if ($this->price == 0) {
                    $percent = 1;
                } else {
                    $percent = $priceInclTax/$this->price;
                }
                $this->taxClasses[$product->getTaxClassId()] = $percent;
            }
            $result[$product->getId()] = [
                'price' => number_format($percent * $this->price, 2, '.', ' '),
                'price_ex' => number_format($percent *  $this->price, 2, '.', ' '),
                'final_price' => number_format($percent * $this->finalPrice, 2, '.', ' '),
                'final_price_ex' => number_format($percent * $this->finalPrice, 2, '.', ' '),
                'sales_price' => number_format($percent * $this->salesPrice, 2, '.', ' '),
                'min_price' => number_format($percent * $this->minPrice, 2, '.', ' '),
                'max_price' => number_format($percent * $this->maxPrice, 2, '.', ' '),
                'total_price' => number_format($percent * $this->totalPrice, 2, '.', ' '),
                'sales_date_range' => $this->getSpecialPriceDateRang($product),
                'discount_perc' => $this->getDiscountPercentage(),
                'tax' => abs(1 - $percent) * 100
            ];
        }
        return $result;
    }

    /**
     * @param Product $product
     * @param string $groupedPriceType
     * @param string $bundlePriceType
     */
    private function setPrices($product, $groupedPriceType, $bundlePriceType)
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

        if ($this->minPrice !== null && $this->price == null) {
            $this->price = $this->minPrice;
        }

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
    private function setConfigurablePrices($product)
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
        $this->specialPrice = $product->getSpecialPrice();
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
    private function setBundlePrices($product, $bundlePriceType)
    {
        $this->price = $product->getPrice() != 0 ? $product->getPrice() : null;
        $this->finalPrice = $product->getFinalPrice() != 0 ? $product->getFinalPrice() : null;
        $this->specialPrice = $product->getSpecialPrice() != 0 ? $product->getFinalPrice() : null;
        $this->minPrice = $product['min_price'] >= 0 ? $product['min_price'] : null;
        $this->maxPrice = $product['max_price'] >= 0 ? $product['max_price'] : null;

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
        $this->price = $product->getPrice() != 0 ? $product->getPrice() : null;
        $this->finalPrice = $product->getFinalPrice() != 0 ? $product->getFinalPrice() : null;
        $this->specialPrice = $product->getSpecialPrice() != 0 ? $product->getFinalPrice() : null;
        $this->minPrice = (float)$product['min_price'] >= 0 ? $product['min_price'] : null;
        $this->maxPrice = (float)$product['max_price'] >= 0 ? $product['max_price'] : null;
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
            $this->logger->error($exception->getMessage());
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
        if ((float)$this->price > 0 && (float)$this->salesPrice > 0) {
            $discount = ((float)$this->salesPrice - (float)$this->price) / (float)$this->price;
            $discount = $discount * -100;
            if ($discount > 0) {
                return round($discount, 1) . '%';
            }
        }
        return '0%';
    }

    /**
     * @return int
     */
    private function getWebsiteId()
    {
        try {
            return (int)$this->storeManager->getStore($this->storeId)->getWebsiteId();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return  0;
    }

    /**
     * @return array
     */
    public function getRequiredParameters()
    {
        return self::REQIURE;
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

    /**
     * @param string $type
     * @param string|\Magento\Catalog\Model\ResourceModel\Product\Collection $data
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
}
