<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Datatrics\Connect\Api\Sales\RepositoryInterface as SaleRepository;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Sales\Model\Order;

/**
 * Class OrderSave
 */
class OrderSave
{

    /**
     * @var SaleRepository
     */
    protected $saleRepository;

    /**
     * @var ProfileRepository
     */
    protected $profileRepository;

    /**
     * @var CustomerCollection
     */
    protected $customerCollection;

    /**
     * OrderPlaced constructor.
     * @param ProfileRepository $profileRepository
     * @param SaleRepository $saleRepository
     * @param CustomerCollection $customerCollection
     */
    public function __construct(
        ProfileRepository $profileRepository,
        SaleRepository $saleRepository,
        CustomerCollection $customerCollection
    ) {
        $this->profileRepository = $profileRepository;
        $this->saleRepository = $saleRepository;
        $this->customerCollection = $customerCollection;
    }

    /**
     * @param Order $order
     * @return Order
     */
    public function afterSave(Order $order)
    {
        $this->saleRepository->prepareSaleData($order);
        if ($order->getCustomerIsGuest()) {
            $this->profileRepository->prepareGuestProfileData($order);
        } else {
            $customers = $this->customerCollection->addFieldToFilter('entity_id', $order->getCustomerId());
            if (!$customers->getSize()) {
                $this->profileRepository->prepareGuestProfileData($order);
            } else {
                $this->profileRepository->prepareProfileData($customers->getFirstItem());
            }
        }
        return $order;
    }
}
