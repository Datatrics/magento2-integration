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

    public const ENTITY_TABLE = 'datatrics_content';
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
     * @param string $field
     * @return bool
     */
    public function isExists($entityId, $field = 'entity_id')
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

    /**
     * Check is entity exists
     *
     * @param int $contentId
     * @return int
     */
    public function getIdByContent($contentId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable(self::ENTITY_TABLE),
            self::PRIMARY
        );
        $select->where('content_id = :content_id');
        $bind = [':content_id' => $contentId];
        return (int)$connection->fetchOne($select, $bind);
    }
}
