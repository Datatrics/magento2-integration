<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Inventory Option Source model
 */
class Inventory implements OptionSourceInterface
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
                ['value' => 'qty', 'label' => __('QTY')],
                ['value' => 'min_sale_qty', 'label' => __('Minimum Sales QTY')],
                ['value' => 'qty_increments', 'label' => __('QTY Increments')],
                ['value' => 'manage_stock', 'label' => __('Manage Stock')],
                ['value' => 'is_in_stock', 'label' => __('Is In Stock')],
            ];
        }
        return $this->options;
    }
}
