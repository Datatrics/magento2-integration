<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Observer;

use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class SaveAddress
 * Prepare profile data from customer when address saved
 */
class SaveAddress implements ObserverInterface
{

    /**
     * @var ContentResource
     */
    protected $contentResource;

    /**
     * @var ProfileRepository
     */
    protected $profileRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * SaveAddress constructor.
     * @param ContentResource $contentResource
     * @param ProfileRepository $profileRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        ContentResource $contentResource,
        ProfileRepository $profileRepository,
        CustomerRepository $customerRepository
    ) {
        $this->contentResource = $contentResource;
        $this->customerRepository = $customerRepository;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Save profile data from customer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customerId = $observer->getEvent()->getCustomerAddress()->getParentId();
        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $this->profileRepository->prepareProfileData($customer, true);
            } catch (\Exception $e) {
                return;
            }
        }
    }
}
