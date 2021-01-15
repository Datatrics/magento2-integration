<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * CategoryTypeList Option Source model
 */
class CategoryTypeList implements OptionSourceInterface
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
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $this->options = [
                ['value' => 'in', 'label' => __('Include by Category')],
                ['value' => 'nin', 'label' => __('Exclude by Category')],
            ];
        }
        return $this->options;
    }
}
