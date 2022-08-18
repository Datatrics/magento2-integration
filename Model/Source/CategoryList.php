<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * CategoryList Option Source model
 */
class CategoryList implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = [];
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * CategoryList constructor.
     *
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            try {
                foreach ($this->toArray() as $key => $value) {
                    $this->options[] = [
                        'value' => $key,
                        'label' => $value
                    ];
                }
            } catch (\Exception $exception) {
                return $this->options;
            }
        }
        return $this->options;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toArray(): array
    {
        $categoryList = [];
        foreach ($this->getCategoryCollection() as $category) {
            $categoryList[$category->getEntityId()] = [
                'name' => $category->getName(),
                'path' => $category->getPath()
            ];
        }

        $catagoryArray = [];
        foreach ($categoryList as $k => $v) {
            if ($path = $this->getCategoryPath($v['path'], $categoryList)) {
                $catagoryArray[$k] = $path;
            }
        }

        asort($catagoryArray);

        return $catagoryArray;
    }

    /**
     * @return Collection
     * @throws LocalizedException
     */
    public function getCategoryCollection()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['path', 'name']);

        return $collection;
    }

    /**
     * @param string $path
     * @param array $categoryList
     *
     * @return string
     */
    public function getCategoryPath(string $path, array $categoryList): string
    {
        $categoryPath = [];
        $rootCats = [1, 2];
        $path = explode('/', $path);

        foreach ($path as $catId) {
            if (!in_array($catId, $rootCats)) {
                if (!empty($categoryList[$catId]['name'])) {
                    $categoryPath[] = $categoryList[$catId]['name'];
                }
            }
        }

        if (!empty($categoryPath)) {
            return implode(' » ', $categoryPath);
        }

        return '';
    }
}
