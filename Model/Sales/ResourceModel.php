<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Sales;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Datatrics sales resource class
 */
class ResourceModel extends AbstractDb
{

    public const ENTITY_TABLE = 'datatrics_sales';
    public const PRIMARY = 'entity_id';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(self::ENTITY_TABLE, self::PRIMARY);
    }

    /**
     * Check is entity exists
     *
     * @param int $entityId
     * @return bool
     */
    public function isExists($entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable(self::ENTITY_TABLE),
            self::PRIMARY
        )->where('entity_id = :entity_id');
        $bind = [':entity_id' => $entityId];
        return (bool)$connection->fetchOne($select, $bind);
    }

    /**
     * Check is entity exists
     *
     * @param int $orderId
     * @return int
     */
    public function getIdByOrder($orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable(self::ENTITY_TABLE),
            self::PRIMARY
        );
        $select->where('order_id = :order_id');
        $bind = [':order_id' => $orderId];
        return (int)$connection->fetchOne($select, $bind);
    }
}
