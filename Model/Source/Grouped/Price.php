<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source\Grouped;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Atrributes Option Source model
 */
class Price implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => '', 'label' => __('Minimum Price (Recommended)')],
                ['value' => 'max', 'label' => __('Maximum Price')],
                ['value' => 'total', 'label' => __('Total Price')]
            ];
        }
        return $this->options;
    }
}
