<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Datatrics\Connect\Api\Sales\RepositoryInterface as SaleRepository;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
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
     * @var ContentRepository
     */
    private $contentRepository;

    /**
     * @var ContentResource
     */
    private $contentResource;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * OrderPlaced constructor.
     * @param ProfileRepository $profileRepository
     * @param SaleRepository $saleRepository
     * @param CustomerCollection $customerCollection
     * @param ContentRepository $contentRepository
     * @param ContentResource $contentResource
     * @param LogRepository $logRepository
     */
    public function __construct(
        ProfileRepository $profileRepository,
        SaleRepository $saleRepository,
        CustomerCollection $customerCollection,
        ContentRepository $contentRepository,
        ContentResource $contentResource,
        LogRepository $logRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->saleRepository = $saleRepository;
        $this->customerCollection = $customerCollection;
        $this->contentRepository = $contentRepository;
        $this->contentResource = $contentResource;
        $this->logRepository = $logRepository;
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
            $customers = $this->customerCollection->addFieldToFilter('entity_id', $order->getCustomerId())
                ->setPageSize(1);
            if (!$customers->getSize()) {
                $this->profileRepository->prepareGuestProfileData($order);
            } else {
                $this->profileRepository->prepareProfileData($customers->getFirstItem());
            }
        }
        $productIds = [];
        foreach ($order->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
        }
        $this->invalidateProducts($productIds);
        return $order;
    }

    /**
     * @param array $productIds
     */
    private function invalidateProducts($productIds = [])
    {
        $connection = $this->contentResource->getConnection();
        foreach ($productIds as $productId) {
            $this->logRepository->addDebugLog('Product', 'ID ' . $productId . ' invalidated');
        }
        $connection->update(
            $this->contentResource->getTable('datatrics_content_store'),
            ['status' => 'Queued for Update'],
            ['product_id in (?)' => $productIds]
        );
    }
}
