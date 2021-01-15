<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 *
 * Source class for status options
 *
 */
class Status extends AbstractSource
{

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            ['value' => 'Queued for Update', 'label' =>__('Queued for Update')->render()],
            ['value' => 'Synced', 'label' => __('Synced')->render()],
            ['value' => 'Error', 'label' => __('Error')->render()],
            ['value' => 'Failed', 'label' => __('Failed')->render()],
        ];
    }
}
