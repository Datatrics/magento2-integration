<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

/**
 * Class OrderGridCollection
 * Add datatrics data to order grid
 */
class OrderGridCollection
{

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $collection
     * @param string $requestName
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'sales_order_grid_data_source') {
            return $collection;
        }

        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            $joinTable = $collection->getResource()->getTable('datatrics_sales');
            $collection->getSelect()->joinLeft(
                ['datatrics_sales' => $joinTable],
                'main_table.entity_id = datatrics_sales.order_id',
                ['datatrics_status' => 'status']
            );
        }
        $collection->addFilterToMap('datatrics_status', 'datatrics_sales.status');
        return $collection;
    }
}
