<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Credentials;

use Magento\Backend\App\Action;
use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Exception;

/**
 * Class Check
 *
 * AJAX controller to is provided credentials correct
 */
class Check extends Action
{

    /**
     * @var ApiAdapter
     */
    private $adapter;
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ApiAdapter $adapter
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ApiAdapter $adapter
    ) {
        $this->adapter = $adapter;
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
            $this->adapter->setCredentials($apiKey, $projectId);
            $success = $this->adapter->execute(ApiAdapter::GET_PROFILES)['success'];
        } catch (Exception $exception) {
            return $resultJson->setData(['error' => false, 'msg' => $exception->getMessage()]);
        }
        if ($success) {
            $message = __('Credentials correct!') . '<br>';
        } else {
            $message = __('Incorrect credentials, please try again');
            return $resultJson->setData(['error' => false, 'msg' => $message]);
        }

        return $resultJson->setData(['success' => true, 'msg' => $message]);
    }
}
