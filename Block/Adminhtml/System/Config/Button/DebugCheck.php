<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\System\Config\Button;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Debug log check button class
 */
class DebugCheck extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Datatrics_Connect::system/config/button/debug.phtml';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Credentials constructor.
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getDebugCheckUrl()
    {
        return $this->getUrl('datatrics/log/debug');
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        $buttonData = ['id' => 'mm-button_debug', 'label' => __('Check last 100 debug log records')];
        try {
            $button = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData($buttonData);
            return $button->toHtml();
        } catch (\Exception $e) {
            return false;
        }
    }
}
