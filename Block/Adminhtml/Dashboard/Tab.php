<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\Dashboard;

use Magento\Backend\Block\Dashboard\Grids;

/**
 * Class Tab
 */
class Tab extends Grids
{

    protected function _prepareLayout()
    {
        $layout = parent::_prepareLayout();
        $this->addTab(
            'datatrics',
            [
                'label' => __('Datatrics'),
                'url' => $this->getUrl('datatrics/*/tab', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        return $layout;
    }
}
