<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\System\Config\Form\Field\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Catalog\Model\Product\Type as ProductTypesSource;

/**
 * HTML select for Product Types
 */
class ProductTypes extends Select
{

    /**
     * @var ProductTypesSource
     */
    private $source;

    /**
     * ProductTypes constructor.
     *
     * @param Context            $context
     * @param ProductTypesSource $source
     * @param array              $data
     */
    public function __construct(
        Context $context,
        ProductTypesSource $source,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->source = $source;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->source->toOptionArray() as $type) {
                $this->addOption($type['value'], $type['label']);
            }
        }

        return parent::_toHtml();
    }

    /**
     * Sets name for input element.
     *
     * @param string $value
     *
     * @return mixed
     */
    public function setInputName(string $value)
    {
        return $this->setData('name', $value);
    }
}
