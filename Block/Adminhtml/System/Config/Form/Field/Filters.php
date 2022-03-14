<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface as ElementBlockInterface;

/**
 * Represents a table for collection filters in the admin configuration
 */
class Filters extends AbstractFieldArray
{

    public const OPTION_PATTERN = 'option_%s';
    public const SELECTED = 'selected="selected"';

    public const RENDERERS = [
        'attribute' => Renderer\Attributes::class,
        'condition' => Renderer\Conditions::class,
        'product_type' => Renderer\ProductTypes::class,
    ];

    /**
     * @var array
     */
    private $renderers;

    /**
     * Render block.
     */
    public function _prepareToRender()
    {
        $this->addColumn('attribute', [
            'label'    => (string)__('Attribute'),
            'class' => 'required-entry',
            'renderer' => $this->getRenderer('attribute')
        ]);
        $this->addColumn('condition', [
            'label'    => (string)__('Condition'),
            'class' => 'required-entry',
            'renderer' => $this->getRenderer('condition')
        ]);
        $this->addColumn('value', [
            'label' =>(string) __('Value'),
            'class' => 'required-entry'
        ]);
        $this->addColumn('product_type', [
            'label' => (string)__('Apply To'),
            'class' => 'required-entry',
            'renderer' => $this->getRenderer('product_type')
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = (string)__('Add');
    }

    /**
     * Returns render according defined type.
     *
     * @return ElementBlockInterface
     */
    public function getRenderer($type)
    {
        if (!isset($this->renderers[$type])) {
            try {
                $this->renderers[$type] = $this->getLayout()->createBlock(
                    self::RENDERERS[$type],
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
            } catch (LocalizedException $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
        return $this->renderers[$type];
    }

    /**
     * Prepare existing row data object.
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    public function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        foreach (['attribute', 'condition', 'product_type'] as $element) {
            if ($elementData = $row->getData($element)) {
                $options[
                    sprintf(
                        self::OPTION_PATTERN,
                        $this->getRenderer($element)->calcOptionHash($elementData)
                    )
                ] = self::SELECTED;
            }
        }
        $row->setData('option_extra_attrs', $options);
    }
}
