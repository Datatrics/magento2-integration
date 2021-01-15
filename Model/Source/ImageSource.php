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
 * ImageSource Option Source model
 */
class ImageSource implements OptionSourceInterface
{

    /**
     * @var array
     */
    public $options;
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
     * @param AttributeRepository $attributeRepository
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
            $options[] = $this->getMediaImageArray();
            $options[] = $this->getMultipleImages();
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function getMediaImageArray(): array
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

        return ['label' => __('Single Source'), 'value' => $imageSource, 'optgroup-name' => __('single-source')];
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
    public function getMultipleImages(): array
    {
        $imageSource[] = ['value' => 'all', 'label' => __('All Images')];
        return ['label' => __('Other Options'), 'value' => $imageSource, 'optgroup-name' => __('other-options')];
    }
}
