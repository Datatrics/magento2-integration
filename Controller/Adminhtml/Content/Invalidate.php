<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Content;

use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Backend\App\Action;
use Magento\Framework\App\Response\RedirectInterface;
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
     * Error Message: not enabled
     */
    public const ERROR_MSG_ENABLED = 'Content sync not enabled for this store, please enable this first.';

    /**
     * Error Message: no items available
     */
    public const ERROR_MSG_NO_ITEMS = 'No product(s) available to invalidate.';

    /**
     * Success Message: update
     */
    public const SUCCESS_MSG = '%1 product(s) were invalidated and queued for update.';

    /**
     * @var ContentResource
     */
    private $contentResource;
    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param ContentResource $contentResource
     * @param ContentConfigRepository $contentConfigRepository
     */
    public function __construct(
        Action\Context $context,
        ContentResource $contentResource,
        ContentConfigRepository $contentConfigRepository,
        RedirectInterface $redirect
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->contentResource = $contentResource;
        $this->contentConfigRepository = $contentConfigRepository;
        $this->redirect = $redirect;
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
                $this->redirect->getRefererUrl()
            );
        }

        $connection = $this->contentResource->getConnection();
        $where = [
            'store_id = ?' => $storeId
        ];
        $count = $connection->update(
            $this->contentResource->getTable('datatrics_content_store'),
            ['status' => 'Queued for Update'],
            $where
        );

        if ($count > 0) {
            $msg = self::SUCCESS_MSG;
            $this->messageManager->addSuccessMessage(__($msg, $count));
        } else {
            $msg = self::ERROR_MSG_NO_ITEMS;
            $this->messageManager->addNoticeMessage(__($msg));
        }

        return $resultRedirect->setPath(
            $this->redirect->getRefererUrl()
        );
    }
}
