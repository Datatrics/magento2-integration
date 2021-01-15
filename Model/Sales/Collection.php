<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Sales;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Datatrics sales collection class
 *
 */
class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            Data::class,
            ResourceModel::class
        );
    }
}
