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
use Magento\Framework\View\Element\BlockInterface;

/**
 * Represents a table for extra product fields in the admin configuration
 */
class ExtraFields extends AbstractFieldArray
{

    /**
     * @var Renderer\Attributes|BlockInterface|null
     */
    private $attributeRenderer = null;

    /**
     * Render block
     */
    public function _prepareToRender()
    {
        $this->addColumn('name', [
            'label' => (string)__('Fieldname'),
        ]);
        $this->addColumn('attribute', [
            'label'    => (string)__('Attribute'),
            'renderer' => $this->getAttributeRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = (string)__('Add');
    }

    /**
     * Returns render of stores
     *
     * @return Renderer\Attributes|BlockInterface
     * @throws LocalizedException
     */
    public function getAttributeRenderer()
    {
        if (!$this->attributeRenderer) {
            try {
                $this->attributeRenderer = $this->getLayout()->createBlock(
                    Renderer\Attributes::class,
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
            } catch (LocalizedException $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }

        return $this->attributeRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     */
    public function _prepareArrayRow(DataObject $row)
    {
        $attribute = $row->getData('attribute');
        $options = [];
        if ($attribute) {
            $options['option_' . $this->getAttributeRenderer()->calcOptionHash($attribute)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
