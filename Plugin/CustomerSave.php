<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Magento\Customer\Model\Customer;

/**
 * Class CustomerSave
 * Process customer data after it saved
 */
class CustomerSave
{

    /**
     * @var ProfileRepository
     */
    protected $profileRepository;

    /**
     * OrderPlaced constructor.
     * @param ProfileRepository $profileRepository
     */
    public function __construct(
        ProfileRepository $profileRepository
    ) {
        $this->profileRepository = $profileRepository;
    }

    /**
     * Prepare data from customer and save datatrics profile
     *
     * @param Customer $customer
     *
     * @return Customer
     */
    public function afterSave(Customer $customer)
    {
        $this->profileRepository->prepareProfileData($customer, true);
        return $customer;
    }
}
