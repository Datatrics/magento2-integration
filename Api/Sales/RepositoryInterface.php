<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Sales;

use Datatrics\Connect\Api\Sales\DataInterface as SalesData;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

/**
 * Sales repository interface
 */
interface RepositoryInterface
{

    /**
     * Input exception text
     */
    public const INPUT_EXCEPTION = 'An ID is needed. Set the ID and try again.';
    /**
     * "No such entity" exception text
     */
    public const NO_SUCH_ENTITY_EXCEPTION = 'The entity with id "%1" does not exist.';
    /**
     * "Could not delete" exception text
     */
    public const COULD_NOT_DELETE_EXCEPTION = 'Could not delete the entity: %1';
    /**
     * "Could not save" exception text
     */
    public const COULD_NOT_SAVE_EXCEPTION = 'Could not save the entity: %1';

    /**
     * Status array
     */
    public const STATUS = [
        'queued' => 'Queued for Update',
        'synced' => 'Synced',
        'error' => 'Error',
        'failed' => 'Failed'
    ];

    /**
     * Loads a specified entity
     *
     * @param int $entityId
     *
     * @return SalesData
     * @throws LocalizedException
     */
    public function get(int $entityId): SalesData;

    /**
     * Return Sales object
     *
     * @return SalesData
     */
    public function create(): DataInterface;

    /**
     * Retrieves a Sales matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Register entity to delete
     *
     * @param SalesData $entity
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(SalesData $entity): bool;

    /**
     * Deletes a Sales entity by ID
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $entityId): bool;

    /**
     * Perform persist operations for one entity
     *
     * @param SalesData $entity
     *
     * @return SalesData
     * @throws LocalizedException
     */
    public function save(SalesData $entity): SalesData;

    /**
     * @param Order $order
     * @return bool
     */
    public function prepareSaleData(Order $order): bool;
}
