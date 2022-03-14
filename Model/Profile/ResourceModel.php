<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Profile;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Datatrics profile resource class
 */
class ResourceModel extends AbstractDb
{

    public const ENTITY_TABLE = 'datatrics_profile';
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
     * @param int $profileId
     * @return int
     */
    public function getIdByProfile($profileId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable(self::ENTITY_TABLE),
            self::PRIMARY
        );
        $select->where('profile_id = ?', $profileId);
        return (int)$connection->fetchOne($select);
    }
}
