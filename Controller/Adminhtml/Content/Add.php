<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Content;

use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Datatrics\Connect\Model\Command\ContentAdd;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Add
 *
 * Controller to add products to config section
 */
class Add extends Action
{

    /**
     * @var ContentResource
     */
    private $contentResource;

    /**
     * @var ContentAdd
     */
    private $contentAdd;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param ContentResource $contentResource
     * @param ContentAdd $contentAdd
     */
    public function __construct(
        Action\Context $context,
        ContentResource $contentResource,
        ContentAdd $contentAdd
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->contentResource = $contentResource;
        $this->contentAdd = $contentAdd;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $storeId = $this->getRequest()->getParam('store_id');
        $count = $this->contentAdd->addProducts($storeId);
        $this->messageManager->addSuccessMessage(__('%1 product(s) was added', $count));
        return $resultRedirect->setPath(
            $this->_redirect->getRefererUrl()
        );
    }
}
