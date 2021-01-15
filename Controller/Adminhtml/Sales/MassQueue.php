<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Sales;

use Datatrics\Connect\Api\Sales\RepositoryInterface as SalesRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Mass add orders to datatrics queue
 */
class MassQueue extends Action
{
    /**
     * @var OrderCollection
     */
    private $orderCollection;

    /**
     * @var SalesRepository
     */
    private $salesRepository;

    /**
     * MassQueue constructor.
     * @param OrderCollection $orderCollection
     * @param SalesRepository $salesRepository
     * @param Context $context
     */
    public function __construct(
        OrderCollection $orderCollection,
        SalesRepository $salesRepository,
        Context $context
    ) {
        $this->orderCollection = $orderCollection;
        $this->salesRepository = $salesRepository;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $added = 0;
        $skipped = 0;
        $orders = $this->getRequest()->getParam('selected');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($orders) {
            $this->orderCollection->addFieldToFilter(
                'entity_id',
                $orders
            );
        }
        foreach ($this->orderCollection as $order) {
            $result = $this->salesRepository->prepareSaleData($order);
            if ($result) {
                $added++;
            } else {
                $skipped++;
            }
        }
        $this->messageManager->addSuccessMessage(
            __('%1 order(s) added, %2 order(s) skipped.', $added, $skipped)
        );
        return $resultRedirect->setPath('sales/order');
    }
}
