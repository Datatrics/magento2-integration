<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Content;

use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
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
     * Error Message: not enabled
     */
    const ERROR_MSG_ENABLED = 'Content sync not enabled for this store, please enable this first.';

    /**
     * Error Message: no items available
     */
    const ERROR_MSG_NO_ITEMS = 'Could not find any products to update, please invalidate the items.';

    /**
     * Success Message: update
     */
    const SUCCESS_MSG = '%1 product(s) were updated. ';

    /**
     * @var ContentResource
     */
    private $contentResource;
    /**
     * @var ContentUpdate
     */
    private $contentUpdate;
    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;

    /**
     * Update constructor.
     *
     * @param Action\Context $context
     * @param ContentResource $contentResource
     * @param ContentUpdate $contentUpdate
     * @param ContentConfigRepository $contentConfigRepository
     */
    public function __construct(
        Action\Context $context,
        ContentResource $contentResource,
        ContentUpdate $contentUpdate,
        ContentConfigRepository $contentConfigRepository
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->contentResource = $contentResource;
        $this->contentUpdate = $contentUpdate;
        $this->contentConfigRepository = $contentConfigRepository;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $storeId = (int)$this->getRequest()->getParam('store_id');

        if (!$this->contentConfigRepository->isEnabled($storeId)) {
            $msg = self::ERROR_MSG_ENABLED;
            $this->messageManager->addErrorMessage(__($msg));
            return $resultRedirect->setPath(
                $this->_redirect->getRefererUrl()
            );
        }

        $connection = $this->contentResource->getConnection();
        $selectProductIds = $connection->select()->from(
            $this->contentResource->getTable('datatrics_content_store'),
            ['product_id']
        )->where('status = ?', 'Queued for Update')
            ->where('store_id = ?', $storeId);
        $productIds = $connection->fetchCol($selectProductIds, 'product_id');
        $count = $this->contentUpdate->prepareData($productIds, $storeId);

        if ($count > 0) {
            $msg = self::SUCCESS_MSG;
            $this->messageManager->addSuccessMessage(__($msg, $count));
        } else {
            $msg = self::ERROR_MSG_NO_ITEMS;
            $this->messageManager->addNoticeMessage(__($msg));
        }

        return $resultRedirect->setPath(
            $this->_redirect->getRefererUrl()
        );
    }
}
