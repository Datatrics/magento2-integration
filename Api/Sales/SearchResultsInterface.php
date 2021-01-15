<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Sales;

use Magento\Framework\Api\SearchResultsInterface
    as FrameworkSearchResultsInterface;

/**
 * Interface for Datatrics Sales search results.
 * @api
 */
interface SearchResultsInterface extends FrameworkSearchResultsInterface
{

    /**
     * Gets Code Items.
     *
     * @return DataInterface[]
     */
    public function getItems() : array;

    /**
     * Sets Code Items.
     *
     * @param DataInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items) : self;
}
