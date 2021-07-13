<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Dashboard;

use Datatrics\Connect\Block\Adminhtml\System\Config\Form\Table\Content;
use Magento\Backend\Controller\Adminhtml\Dashboard\AjaxBlock;
use Magento\Framework\Controller\Result\Raw;

/**
 * Class Tab
 */
class Tab extends AjaxBlock
{

    /**
     * @return Raw
     */
    public function execute()
    {
        $output = $this->layoutFactory->create()
            ->createBlock(Content::class)
            ->setId('datatricsDashboard')
            ->setTemplate('Datatrics_Connect::dashboard/tab.phtml')
            ->toHtml();
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($output);
    }
}
