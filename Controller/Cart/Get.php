<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Cart;

use Magento\Checkout\Model\SessionFactory as Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Datatrics\Connect\ViewModel\PreProcessor;

/**
 * Class Get
 * Ajax controller to get queued events
 */
class Get extends Action implements HttpPostActionInterface
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var PreProcessor
     */
    private $preProcessor;

    /**
     * Get constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Session $checkoutSession
     * @param PreProcessor $preProcessor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Session $checkoutSession,
        PreProcessor $preProcessor
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->preProcessor = $preProcessor;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $html = '';
        if ($this->checkoutSession->create()->getCartTrigger()) {
            $html = $this->preProcessor->getTrack(
                'Datatrics_Connect::cart.phtml',
                'cart'
            );
        }
        $this->checkoutSession->create()->setCartTrigger(false);
        $result->setData($html);
        return $result;
    }
}
