<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Content;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Datatrics\Connect\Api\Content\DataInterface
    as ContentData;
use Datatrics\Connect\Api\Content\SearchResultsInterface;
use Magento\Customer\Model\Customer;

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
     *
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
     * @return ContentData
     * @throws LocalizedException
     */
    public function get(int $entityId) : ContentData;

    /**
     * Return Content object
     *
     * @return ContentData
     */
    public function create();

    /**
     * Retrieves an Content matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface;

    /**
     * Register entity to delete
     *
     * @param ContentData $entity
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ContentData $entity) : bool;

    /**
     * Deletes a Content entity by ID
     *
     * @param int $entity
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $entity) : bool;

    /**
     * Perform persist operations for one entity
     *
     * @param ContentData $entity
     *
     * @return ContentData
     * @throws LocalizedException
     */
    public function save(ContentData $entity) : ContentData;

    /**
     * @param array $productIds
     * @return mixed
     */
    public function prepareContentData(array $productIds);
}
