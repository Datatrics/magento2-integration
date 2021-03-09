<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\ProductData;

/**
 * Product data repository interface
 */
interface RepositoryInterface
{

    /**
     * Get formatted product data
     *
     * @param int $storeId
     * @param array $entityIds
     * @return array
     */
    public function getProductData(int $storeId = 0, array $entityIds = []): array;
}
