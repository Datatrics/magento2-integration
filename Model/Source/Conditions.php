<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Conditions Option Source model
 */
class Conditions implements OptionSourceInterface
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
                    'value' => '',
                    'label' => ''
                ],
                [
                    'value' => 'eq',
                    'label' => __('Equal')
                ],
                [
                    'value' => 'neq',
                    'label' => __('Not equal')
                ],
                [
                    'value' => 'gt',
                    'label' => __('Greater than')
                ],
                [
                    'value' => 'gteq',
                    'label' => __('Greater than or equal to')
                ],
                [
                    'value' => 'lt',
                    'label' => __('Less than')
                ],
                [
                    'value' => 'lteg',
                    'label' => __('Less than or equal to')
                ],
                [
                    'value' => 'in',
                    'label' => __('In')
                ],
                [
                    'value' => 'nin',
                    'label' => __('Not in')
                ],
                [
                    'value' => 'like',
                    'label' => __('Like')
                ],
                [
                    'value' => 'empty',
                    'label' => __('Empty')
                ],
                [
                    'value' => 'not-empty',
                    'label' => __('Not Empty')
                ],
            ];
        }
        return $this->options;
    }
}
