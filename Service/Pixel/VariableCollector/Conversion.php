<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Pixel\VariableCollector;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Conversion
 */
class Conversion
{

    /**
     * @var Session
     */
    private $session;

    /**
     * @var OrderRepositoryInterface
     */
    private $order;

    /**
     * Conversion constructor.
     * @param Session $session
     * @param OrderRepositoryInterface $order
     */
    public function __construct(
        Session $session,
        OrderRepositoryInterface $order
    ) {
        $this->session = $session;
        $this->order = $order;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $orderId = $this->session->getLastOrderId();
        $order = $this->order->get((int)$orderId);
        $result = [
            'products' => [],
            'orderid' => $order->getIncrementId(),
            'grandtotal' => $order->getGrandTotal(),
            'subtotal' => $order->getSubtotal(),
            'tax' => $order->getTaxAmount(),
            'shipping' => $order->getShippingAmount(),
            'discount' =>  $order->getDiscountAmount()
        ];
        foreach ($order->getAllVisibleItems() as $item) {
            $result['products'][] = [
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQtyOrdered(),
            ];
        }
        return $result;
    }
}
