<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Content;

use Datatrics\Connect\Model\Command\ContentUpdate;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Update
 *
 * Controller to update products from config section
 */
class Update extends Action
{

    /**
     * @var ContentResource
     */
    private $contentResource;

    /**
     * @var ContentUpdate
     */
    private $contentUpdate;

    /**
     * Update constructor.
     *
     * @param Action\Context $context
     * @param ContentResource $contentResource
     * @param ContentUpdate $contentUpdate
     */
    public function __construct(
        Action\Context $context,
        ContentResource $contentResource,
        ContentUpdate $contentUpdate
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->contentResource = $contentResource;
        $this->contentUpdate = $contentUpdate;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $connection = $this->contentResource->getConnection();
        $storeId = $this->getRequest()->getParam('store_id');
        $selectProductIds = $connection->select()->from(
            $connection->getTableName('datatrics_content_store'),
            ['product_id']
        )->where('status = ?', 'Queued for Update')
            ->where('store_id = ?', $storeId);
        $productIds = $connection->fetchCol($selectProductIds, 'product_id');
        $count = $this->contentUpdate->prepareData($productIds, $storeId);
        $this->messageManager->addSuccessMessage(__('%1 product(s) was updated', $count));
        return $resultRedirect->setPath(
            $this->_redirect->getRefererUrl()
        );
    }
}
