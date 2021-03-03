<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magmodules\Channable\Setup\Tables\ChannableReturns;

/**
 * Class UpgradeData
 * @package Datatrics\Connect\Setup
 */
class UpgradeData implements UpgradeDataInterface
{

    const FIELDS = [
        'datatrics_connect/general/version'
        => 'datatrics_connect_general/general/version',
        'datatrics_connect/general/enable'
        => 'datatrics_connect_general/general/enable',
        'datatrics_connect/general/api_key'
        => 'datatrics_connect_general/general/api_key',
        'datatrics_connect/general/project_id'
        => 'datatrics_connect_general/general/project_id',
        'datatrics_connect/general/debug'
        => 'datatrics_connect_general/general/debug',
        'datatrics_connect/product_data/sku'
        => 'datatrics_connect_product/product_data/sku',
        'datatrics_connect/product_data/name'
        => 'datatrics_connect_product/product_data/name',
        'datatrics_connect/product_data/description'
        => 'datatrics_connect_product/product_data/description',
        'datatrics_connect/product_data/short_description'
        => 'datatrics_connect_product/product_data/short_description',
        'datatrics_connect/product_data/image'
        => 'datatrics_connect_product/product_data/image',
        'datatrics_connect/product_sync/enable'
        => 'datatrics_connect_product/product_sync/enable',
        'datatrics_connect/product_sync/source'
        => 'datatrics_connect_general/general/source',
        'datatrics_connect/advanced_options/extra_fields'
        => 'datatrics_connect_product/advanced_options/extra_fields',
        'datatrics_connect/advanced_options/inventory'
        => 'datatrics_connect_product/advanced_options/inventory',
        'datatrics_connect/advanced_options/inventory_fields'
        => 'datatrics_connect_product/advanced_options/inventory_fields',
        'datatrics_connect/types/configurable'
        => 'datatrics_connect_product/types/configurable',
        'datatrics_connect/types/configurable_link'
        => 'datatrics_connect_product/types/configurable_link',
        'datatrics_connect/types/configurable_image'
        => 'datatrics_connect_product/types/configurable_image',
        'datatrics_connect/types/configurable_parent_atts'
        => 'datatrics_connect_product/types/configurable_parent_atts',
        'datatrics_connect/types/configurable_nonvisible'
        => 'datatrics_connect_product/types/configurable_nonvisible',
        'datatrics_connect/types/bundle'
        => 'datatrics_connect_product/types/bundle',
        'datatrics_connect/types/bundle_link'
        => 'datatrics_connect_product/types/bundle_link',
        'datatrics_connect/types/bundle_image'
        => 'datatrics_connect_product/types/bundle_image',
        'datatrics_connect/types/bundle_parent_atts'
        => 'datatrics_connect_product/types/bundle_parent_atts',
        'datatrics_connect/types/bundle_nonvisible'
        => 'datatrics_connect_product/types/bundle_nonvisible',
        'datatrics_connect/types/grouped'
        => 'datatrics_connect_product/types/grouped',
        'datatrics_connect/types/grouped_link'
        => 'datatrics_connect_product/types/grouped_link',
        'datatrics_connect/types/grouped_image'
        => 'datatrics_connect_product/types/grouped_image',
        'datatrics_connect/types/grouped_parent_atts'
        => 'datatrics_connect_product/types/grouped_parent_atts',
        'datatrics_connect/types/grouped_nonvisible'
        => 'datatrics_connect_product/types/grouped_nonvisible',
        'datatrics_connect/product_filter/add_disabled'
        => 'datatrics_connect_product/product_filter/add_disabled',
        'datatrics_connect/product_filter/visbility_enabled'
        => 'datatrics_connect_product/product_filter/visbility_enabled',
        'datatrics_connect/product_filter/visbility' => 'datatrics_connect_product/product_filter/visbility',
        'datatrics_connect/product_filter/category_enabled'
        => 'datatrics_connect_product/product_filter/category_enabled',
        'datatrics_connect/product_filter/category_type'
        => 'datatrics_connect_product/product_filter/category_type',
        'datatrics_connect/product_filter/category'
        => 'datatrics_connect_product/product_filter/category',
        'datatrics_connect/product_filter/stock'
        => 'datatrics_connect_product/product_filter/stock',
        'datatrics_connect/product_filter/filters_data' => 'datatrics_connect_product/product_filter/filters_data',
        'datatrics_connect/customer_sync/enable'
        => 'datatrics_connect_customer/customer_sync/enable',
        'datatrics_connect/customer_sync/limit_customer_group'
        => 'datatrics_connect_customer/customer_sync/limit_customer_group',
        'datatrics_connect/customer_sync/customer_group'
        => 'datatrics_connect_customer/customer_sync/customer_group',
        'datatrics_connect/customer_sync/cron'
        => 'datatrics_connect_customer/customer_sync/cron',
        'datatrics_connect/customer_sync/cron_custom'
        => 'datatrics_connect_customer/customer_sync/cron_custom',
        'datatrics_connect/order_sync/enable'
        => 'datatrics_connect_order/order_sync/enable',
        'datatrics_connect/order_sync/limit_order_state'
        => 'datatrics_connect_order/order_sync/limit_order_state',
        'datatrics_connect/order_sync/order_state'
        => 'datatrics_connect_order/order_sync/order_state',
        'datatrics_connect/order_sync/limit_customer_group'
        => 'datatrics_connect_order/order_sync/limit_customer_group',
        'datatrics_connect/order_sync/customer_group'
        => 'datatrics_connect_order/order_sync/customer_group',
        'datatrics_connect/order_sync/cron'
        => 'datatrics_connect_order/order_sync/cron',
        'datatrics_connect/order_sync/cron_custom'
        => 'datatrics_connect_order/order_sync/cron_custom',
        'datatrics_connect/tracking/enable'
        => 'datatrics_connect_tracking/tracking/enable',
        'datatrics_connect/order_sync/limit_order_customer_group'
        => 'datatrics_connect_order/order_sync/limit_order_customer_group',
        'datatrics_connect/types/grouped_parent_price'
        => 'datatrics_connect_product/types/grouped_parent_price',
        'datatrics_connect/tracking/product'
        => 'datatrics_connect_tracking/tracking/product',
        'datatrics_connect/tracking/category'
        => 'datatrics_connect_tracking/tracking/category',
        'datatrics_connect/tracking/cart'
        => 'datatrics_connect_tracking/tracking/cart',
        'datatrics_connect/tracking/conversion'
        => 'datatrics_connect_tracking/tracking/conversion',
        'datatrics_connect/product_sync/cron'
        => 'datatrics_connect_product/product_sync/cron',
        'datatrics_connect/product_filter/filters'
        => 'datatrics_connect_product/product_filter/filters',
        'datatrics_connect/product_data/inventory'
        => 'datatrics_connect_product/product_data/inventory',
        'datatrics_connect/product_data/inventory_fields'
        => 'datatrics_connect_product/product_data/inventory_fields',
        'datatrics_connect/product_data/extra_fields'
        => 'datatrics_connect_product/product_data/extra_fields',
        'datatrics_connect/product_types/configurable'
        => 'datatrics_connect_product/product_types/configurable',
        'datatrics_connect/product_types/configurable_link'
        => 'datatrics_connect_product/product_types/configurable_link',
        'datatrics_connect/product_types/configurable_image'
        => 'datatrics_connect_product/product_types/configurable_image',
        'datatrics_connect/product_types/configurable_parent_atts'
        => 'datatrics_connect_product/product_types/configurable_parent_atts',
        'datatrics_connect/product_types/configurable_nonvisible'
        => 'datatrics_connect_product/product_types/configurable_nonvisible',
        'datatrics_connect/product_types/bundle'
        => 'datatrics_connect_product/product_types/bundle',
        'datatrics_connect/product_types/bundle_link'
        => 'datatrics_connect_product/product_types/bundle_link',
        'datatrics_connect/product_types/bundle_image'
        => 'datatrics_connect_product/product_types/bundle_image',
        'datatrics_connect/product_types/bundle_parent_atts'
        => 'datatrics_connect_product/product_types/bundle_parent_atts',
        'datatrics_connect/product_types/bundle_nonvisible'
        => 'datatrics_connect_product/product_types/bundle_nonvisible',
        'datatrics_connect/product_types/grouped'
        => 'datatrics_connect_product/product_types/grouped',
        'datatrics_connect/product_types/grouped_parent_price'
        => 'datatrics_connect_product/product_types/grouped_parent_price',
        'datatrics_connect/product_sync/cron_custom'
        => 'datatrics_connect_product/product_sync/cron_custom'
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * UpgradeData constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), "1.2.0", "<")) {
            $connection = $this->resourceConnection->getConnection();
            foreach (self::FIELDS as $oldField => $newField) {
                $connection->update(
                    $connection->getTableName('core_config_data'),
                    ['path' => $newField],
                    ['path = ?' => $oldField]
                );
            }
        }
    }
}
