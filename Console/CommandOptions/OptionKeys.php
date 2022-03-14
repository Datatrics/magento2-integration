<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\CommandOptions;

/**
 * Option Keys helper
 *
 * This class contains the list options,
 * which can be used for CLI commands
 * * datatrics:sale:update
 */
class OptionKeys
{
    /**
     * Key for store_id argument
     */
    public const STORE_ID = 'store-id';

    /**
     * Key for product_id argument
     */
    public const PRODUCT_ID = 'product-id';

    /**
     * Key for --limit option
     */
    public const LIMIT = 'limit';

    /**
     * Key for --force option
     */
    public const FORCE = 'force';

    /**
     * Key for --dry option
     */
    public const DRY = 'dry';

    /**
     * Key for --category-id option
     */
    public const CATEGORY_ID = 'category-id';

    /**
     * Key for --from-date option
     */
    public const FROM_DATE = 'from-date';

    /**
     * Key for --to-date option
     */
    public const TO_DATE = 'to-date';

    /**
     * Key for --customer-id option
     */
    public const CUSTOMER_ID = 'customer-id';

    /**
     * Key for --order-id option
     */
    public const ORDER_ID = 'order-id';

    /**
     * Key for --offset option
     */
    public const OFFSET = 'offset';
}
