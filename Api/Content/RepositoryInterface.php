<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Content;

use Datatrics\Connect\Api\Content\DataInterface as ContentData;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface Repository
 *
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
     * Content status key-pair
     */
    public const STATUS = [
        'queued' => 'Queued for Update',
        'synced' => 'Synced',
        'error' => 'Error',
        'skipped' => 'Skipped',
        'failed' => 'Failed'
    ];

    /**
     * Loads a specified entity
     *
     * @param int $entityId
     *
     * @return ContentData
     * @throws LocalizedException
     */
    public function get(int $entityId): ContentData;

    /**
     * Return Content object
     *
     * @return ContentData
     */
    public function create(): DataInterface;

    /**
     * Retrieves a Content matching the specified criteria.
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
     * @param ContentData $entity
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ContentData $entity): bool;

    /**
     * Deletes a Content entity by ID
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
     * @param ContentData $entity
     *
     * @return ContentData
     * @throws LocalizedException
     */
    public function save(ContentData $entity): ContentData;
}
