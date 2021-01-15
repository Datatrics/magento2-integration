<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * ProductVisibility Option Source model
 */
class ProductVisibility implements OptionSourceInterface
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
                ['value' => '1', 'label' => __('Not Visible Individually')],
                ['value' => '2', 'label' => __('Catalog')],
                ['value' => '3', 'label' => __('Search')],
                ['value' => '4', 'label' => __('Catalog, Search')],
            ];
        }
        return $this->options;
    }
}
