<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\Datatrics;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Adding extra heading Block
 */
class Heading extends Field
{

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $html = '<tr id="row_' . $element->getHtmlId() . '">';
        $html .= '  <td class="label"></td>';
        $html .= '  <td class="value">';
        $html .= '     <div class="mm-heading-block">' . $element->getData('label') . '</div>';
        $html .= '     <div class="mm-heading-comment">' . $element->getData('comment') . '</div>';
        $html .= '  </td>';
        $html .= '  <td></td>';
        $html .= '</tr>';

        return $html;
    }
}
