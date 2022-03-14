<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Tracking;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Class Test
 *
 * AJAX controller to is connection correct
 */
class Test extends Action
{

    /**
     * Datarics Sale Endpoint
     */
    public const URL = 'https://api.datatrics.com/2.0/project/%s/sale?apikey=%s&q[goalid]=-1&includecount=0&limit=10';

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Json $json
     * @param Curl $curl
     * @param ConfigRepository $configRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        Json $json,
        Curl $curl,
        ConfigRepository $configRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $context->getMessageManager();
        $this->json = $json;
        $this->curl = $curl;
        $this->configRepository = $configRepository;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $apiKey = $this->configRepository->getApiKey();
        $projectId = $this->configRepository->getProjectId();
        $url = sprintf(
            self::URL,
            $projectId,
            $apiKey
        );
        $resultJson = $this->resultJsonFactory->create();

        $this->curl->get($url);
        $success = true;
        $message = 'Success';
        if ($this->curl->getStatus() != 200) {
            $result = $this->json->unserialize($this->curl->getBody());
            if (array_key_exists('message', $result)) {
                $message = $result['message'];
            } else {
                $message = $result['error']['message'];
            }
        }
        return $resultJson->setData(['success' => $success, 'msg' => $message]);
    }
}
