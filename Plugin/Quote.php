<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\SessionFactory as Session;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Quote
 * Plugin for quote model
 */
class Quote
{
    public const CART_TRIGGER = 'TriggerCart';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * Quote constructor.
     *
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Fire event after item was removed from cart
     *
     * @param QuoteModel $subject
     * @param QuoteModel $result
     * @param int $itemId
     *
     * @return QuoteModel
     */
    public function afterRemoveItem(
        QuoteModel $subject,
        QuoteModel $result,
        int $itemId
    ) {
        $this->checkoutSession->create()->setCartTrigger(true);
        return $result;
    }

    /**
     * Fire event after item was added to the cart (only after post request)
     *
     * @param QuoteModel $subject
     * @param mixed $result
     * @param Product $product
     *
     * @return Item
     */
    public function afterAddProduct(
        QuoteModel $subject,
        $result,
        Product $product
    ) {
        $this->checkoutSession->create()->setCartTrigger(true);
        return $result;
    }
}
