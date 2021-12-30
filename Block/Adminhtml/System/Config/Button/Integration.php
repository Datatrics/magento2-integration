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
 * Class Integration
 *
 * Integration validation
 */
class Integration extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Datatrics_Connect::system/config/button/integration.phtml';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Integration constructor.
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
    public function getIntegrationUrl()
    {
        return $this->getUrl('datatrics/integration/create');
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        $buttonData = ['id' => 'button_integration', 'label' => __('Create Integration')];
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
