<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Credentials;

use Datatrics\Connect\Service\API\ConnectionTest;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Check
 *
 * AJAX controller to is provided credentials correct
 */
class Check extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var ConnectionTest
     */
    private $connectionTest;

    /**
     * Check constructor.
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConnectionTest $connectionTest
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ConnectionTest $connectionTest
    ) {
        $this->connectionTest = $connectionTest;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $apiKey = $this->getRequest()->getParam('api_key');
        $projectId = $this->getRequest()->getParam('project_id');
        $resultJson = $this->resultJsonFactory->create();

        try {
            $this->connectionTest->execute($apiKey, $projectId);
            $message = __('Credentials correct!') . '<br/>';
            return $resultJson->setData(['success' => true, 'msg' => $message]);
        } catch (\Exception $exception) {
            $message = __($exception->getMessage()) . '<br/>';
            return $resultJson->setData(['success' => false, 'msg' => $message]);
        }
    }
}
