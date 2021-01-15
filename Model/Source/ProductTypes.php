<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * ProductTypes Option Source model
 */
class ProductTypes implements OptionSourceInterface
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
                [
                    'value' => 'simple',
                    'label' => __('Simple')
                ],
                [
                    'value' => 'configurable',
                    'label' => __('configurable')
                ],
                [
                    'value' => 'grouped',
                    'label' => __('Grouped')
                ],
                [
                    'value' => 'bundled',
                    'label' => __('Bundled')
                ],
                [
                    'value' => 'downloadable',
                    'label' => __('Downloadable')
                ],
                [
                    'value' => 'virtual',
                    'label' => __('Virtual')
                ]
            ];
        }
        return $this->options;
    }
}
