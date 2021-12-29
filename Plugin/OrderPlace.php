<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Datatrics\Connect\Api\Sales\RepositoryInterface as SaleRepository;
use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Sales\Model\Order;

/**
 * Class OrderPlace
 * Process order data after it placed
 */
class OrderPlace
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
     * Prepare all order related data for platform push
     *
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface
     * @param Order $order
     *
     * @return Order
     */
    public function afterPlace(\Magento\Sales\Api\OrderManagementInterface $orderManagementInterface, $order)
    {
        $this->saleRepository->prepareSaleData($order);
        if ($order->getCustomerIsGuest()) {
            $this->profileRepository->prepareGuestProfileData($order);
        } else {
            if ($order->getCustomerId()) {
                $customers = $this->customerCollection->addFieldToFilter('entity_id', $order->getCustomerId());
                if (!$customers->getSize()) {
                    $this->profileRepository->prepareGuestProfileData($order);
                } else {
                    $customers->setPageSize(1);
                    $this->profileRepository->prepareProfileData($customers->getFirstItem());
                }
            }
        }
        return $order;
    }
}
