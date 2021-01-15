<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * MainImage Option Source model
 */
class MainImage implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Attributes constructor.
     *
     * @param AttributeRepository   $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $options[] = $this->getPositionSource();
            $options[] = $this->getMediaImageTypesSource();
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function getMediaImageTypesSource(): array
    {
        $imageSource = [];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('frontend_input', 'media_image')->create();
        /** @var AbstractAttribute $attribute */
        foreach ($this->attributeRepository->getList($searchCriteria)->getItems() as $attribute) {
            if ($attribute->getIsVisible()) {
                $imageSource[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $this->getLabel($attribute)
                ];
            }
        }
        usort($imageSource, function ($a, $b) {
            return strcmp($a["label"], $b["label"]);
        });

        return ['label' => __('Media Image Types'), 'value' => $imageSource, 'optgroup-name' => __('image-types')];
    }

    /**
     * @param AbstractAttribute $attribute
     *
     * @return string
     */
    public function getLabel($attribute): string
    {
        return str_replace("'", '', $attribute->getFrontendLabel());
    }

    /**
     * @return array
     */
    public function getPositionSource(): array
    {
        $imageSource = [];
        $imageSource[] = ['value' => '', 'label' => __('First Image (default)')];
        $imageSource[] = ['value' => 'last', 'label' => __('Last Image')];
        return ['label' => __('By position'), 'value' => $imageSource, 'optgroup-name' => __('position')];
    }
}
