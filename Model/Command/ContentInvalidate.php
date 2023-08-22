<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;

/**
 * Class ContentInvalidate
 *
 * Invalidate content data
 */
class ContentInvalidate
{

    /**
     * @var ContentResource
     */
    private $contentResource;

    /**
     * ContentInvalidate constructor.
     * @param ContentResource $contentResource
     */
    public function __construct(
        ContentResource $contentResource
    ) {
        $this->contentResource = $contentResource;
    }

    /**
     * @param array $storeIds
     * @param array|null $productIds
     * @return int
     */
    public function run(array $storeIds, ?array $productIds = []): int
    {
        $where = ['store_id IN (?)' => $storeIds];
        if (!empty($productIds)) {
            $where['product_id IN (?)'] = $productIds;
        }

        $connection = $this->contentResource->getConnection();
        return $connection->update(
            $this->contentResource->getTable('datatrics_content_store'),
            ['status' => 'Queued for Update'],
            $where
        );
    }
}
