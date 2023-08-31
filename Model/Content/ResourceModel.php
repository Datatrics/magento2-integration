<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Content;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Datatrics Content resource class
 *
 */
class ResourceModel extends AbstractDb
{

    public const ENTITY_TABLE = 'datatrics_content_store';
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
     * @param string|null $field
     * @return bool
     */
    public function isExists(int $entityId, ?string $field = 'entity_id'): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable(self::ENTITY_TABLE),
            self::PRIMARY
        );
        $select->where($field . ' = :' . $field);
        $bind = [':' . $field => $entityId];
        return (bool)$connection->fetchOne($select, $bind);
    }
}
