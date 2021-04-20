<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Pixel\VariableCollector;

use Magento\Checkout\Model\Cart as MagentoCart;

/**
 * Class Cart
 */
class Cart
{

    /**
     * @var MagentoCart
     */
    private $cart;

    /**
     * Base constructor.
     * @param MagentoCart $cart
     */
    public function __construct(
        MagentoCart $cart
    ) {
        $this->cart = $cart;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $variables = [
            'products' => []
        ];
        foreach ($this->cart->getQuote()->getAllVisibleItems() as $item) {
            $variables['products'][] = [
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQty()
            ];
        }

        $variables['totalcartvalue'] = $this->cart->getQuote()->getGrandTotal();
        return $variables;
    }
}
