<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Profile;

use Datatrics\Connect\Api\Profile\DataInterface as ProfileData;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as DataCustomer;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

/**
 * Profile repository interface
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
     * @return ProfileData
     * @throws LocalizedException
     */
    public function get(int $entityId): ProfileData;

    /**
     * Return Profile object
     *
     * @return ProfileData
     */
    public function create(): ProfileData;

    /**
     * Retrieves an Profile matching the specified criteria.
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
     * @param ProfileData $entity
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(ProfileData $entity): bool;

    /**
     * Deletes a Profile entity by ID
     *
     * @param int $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $entityId): bool;

    /**
     * Perform persist operations for one entity
     *
     * @param ProfileData $entity
     *
     * @return ProfileData
     * @throws LocalizedException
     */
    public function save(ProfileData $entity): ProfileData;

    /**
     * @param DataCustomer|Customer $customer
     * @param bool $forceUpdate
     * @param null $address
     * @return mixed
     */
    public function prepareProfileData($customer, bool $forceUpdate = false, $address = null): bool;

    /**
     * @param Order $order
     * @return mixed
     */
    public function prepareGuestProfileData(Order $order): bool;
}
