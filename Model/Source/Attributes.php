<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Datatrics\Connect\Model\Source;

use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Atrributes Option Source model
 */
class Attributes implements OptionSourceInterface
{

    /**
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
            $options[] = ['value' => '', 'label' => __('None / Do not use')];
            $options[] = $this->getAttributesArray();
            $relations = $this->getRelations();
            if (!empty($relations)) {
                $options[] = $relations;
            }
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function getAttributesArray(): array
    {
        $attributes = [
            ['value' => 'attribute_set_id', 'label' => __('Attribute Set ID')],
            ['value' => 'attribute_set_name', 'label' => __('Attribute Set Name')],
            ['value' => 'type_id', 'label' => __('Product Type')],
            ['value' => 'entity_id', 'label' => __('Product Id')],
        ];

        $exclude = $this->getNonAvailableAttributes();
        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var AbstractAttribute $attribute */
        foreach ($this->attributeRepository->getList($searchCriteria)->getItems() as $attribute) {
            if ($attribute->getIsVisible() && !in_array($attribute->getAttributeCode(), $exclude)) {
                $attributes[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $this->getLabel($attribute)
                ];
            }
        }

        usort($attributes, function ($a, $b) {
            return strcmp($a["label"], $b["label"]);
        });

        return ['label' => __('Atttibutes'), 'value' => $attributes, 'optgroup-name' => __('Atttibutes')];
    }

    /**
     * @return array
     */
    public function getNonAvailableAttributes(): array
    {
        return ['categories', 'category_ids', 'gallery'];
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
    public function getRelations(): array
    {
        return [
            'label' => __('Product Relations'),
            'value' => [
                ['label' => __('Related Skus'), 'value' => 'related_skus'],
                ['label' => __('Upsell Skus'), 'value' => 'upsell_skus'],
                ['label' => __('Crosssell Skus'), 'value' => 'crosssell_skus'],
            ],
            'optgroup-name' => __('Product Relations')
        ];
    }
}
