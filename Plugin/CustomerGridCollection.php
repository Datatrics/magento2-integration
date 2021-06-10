<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Datatrics\Connect\Model\Profile\ResourceModel as ProfileResource;

/**
 * Class CustomerGridCollection
 * Add datatrics data to customer grid
 */
class CustomerGridCollection
{

    const TABLE = 'customer_grid_flat';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::TABLE)) {
            $leftJoinTableName = $collection->getConnection()->getTableName(ProfileResource::ENTITY_TABLE);

            $collection
                ->getSelect()
                ->joinLeft(
                    ['datatrics_profile' => $leftJoinTableName],
                    "datatrics_profile.customer_id = main_table.entity_id",
                    ['status']
                );
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            foreach ($where as &$item) {
                if (strpos($item, 'status') === false) {
                    $item = substr_replace($item, "`main_table`.", strpos($item, '`'), 0);
                }
            }
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
            $collection->addFilterToMap('status', 'datatrics_profile.status');
        }
        return $collection;
    }
}
