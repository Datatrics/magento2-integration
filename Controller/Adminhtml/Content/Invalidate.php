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
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Invalidate
 *
 * Controller to invalidate products from config section
 */
class Invalidate extends Action
{

    /**
     * @var ContentResource
     */
    private $contentResource;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param ContentResource $contentResource
     */
    public function __construct(
        Action\Context $context,
        ContentResource $contentResource
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->contentResource = $contentResource;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $connection = $this->contentResource->getConnection();
        $where = [
            'store_id = ?' => $this->getRequest()->getParam('store_id')
        ];
        $count = $connection->update(
            $connection->getTableName('datatrics_content_store'),
            ['status' => 'Queued for Update'],
            $where
        );
        $this->messageManager->addSuccessMessage(__('%1 product(s) was invalidated', $count));
        return $resultRedirect->setPath(
            $this->_redirect->getRefererUrl()
        );
    }
}
