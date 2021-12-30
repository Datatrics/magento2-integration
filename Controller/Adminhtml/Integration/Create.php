<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Integration;

use Datatrics\Connect\Service\Integration\Create as CreateIntegration;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Create
 *
 * AJAX controller to crete integration
 */
class Create extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CreateIntegration
     */
    private $createIntegration;

    /**
     * Check constructor.
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CreateIntegration $createIntegration
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        CreateIntegration $createIntegration
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $context->getMessageManager();
        $this->createIntegration = $createIntegration;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $storeId = (int)$this->getRequest()->getParam('store_id');

        try {
            $this->createIntegration->deleteIntegration();
            $token = $this->createIntegration->createIntegration($storeId);
            return $resultJson->setData(['success' => true, 'msg' => 'Success!', 'token' => $token]);
        } catch (\Exception $exception) {
            $message = __($exception->getMessage()) . '<br/>';
            return $resultJson->setData(['success' => false, 'msg' => $message, 'token' => null]);
        }
    }
}
