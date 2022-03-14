<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Datatrics\Connect\Model\Profile\ResourceModel as ProfileResource;
use Magento\Customer\Model\ResourceModel\Grid\Collection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

/**
 * Class CustomerGridCollection
 * Add datatrics data to customer grid
 */
class CustomerGridCollection
{

    public const TABLE = 'customer_grid_flat';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * CustomerGridCollection constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Reporting $intercepter
     * @param Collection $collection
     *
     * @return mixed
     * @throws \Zend_Db_Select_Exception
     */
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $this->resourceConnection->getTableName(self::TABLE)) {
            $leftJoinTableName = $this->resourceConnection->getTableName(ProfileResource::ENTITY_TABLE);
            $collection
                ->getSelect()
                ->distinct()
                ->joinLeft(
                    ['datatrics_profile' => $leftJoinTableName],
                    "datatrics_profile.customer_id = main_table.entity_id",
                    ['status' => 'datatrics_profile.status']
                );
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            foreach ($where as &$item) {
                if (strpos($item, "`main_table`.`status`") !== false) {
                    $item = str_replace("`main_table`.`status`", "`datatrics_profile`.`status`", $item);
                }
            }
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
        }
        return $collection;
    }
}
