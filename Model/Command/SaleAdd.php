<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Api\Sales\RepositoryInterface as SaleRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SaleCollection;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Sales\ResourceModel;
use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;

/**
 * Class SaleUpdate
 *
 * Prepare sale data
 */
class SaleAdd
{

    /**
     * @var SaleRepository
     */
    private $saleRepository;

    /**
     * @var SaleCollection
     */
    private $saleCollection;

    /**
     * @var ResourceModel
     */
    private $resource;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var CustomerCollection
     */
    private $customerCollection;

    /**
     * SalesUpdate constructor.
     * @param SaleRepository $saleRepository
     * @param SaleCollection $saleCollection
     * @param ResourceModel $resource
     * @param ProfileRepository $profileRepository
     * @param CustomerCollection $customerCollection
     */
    public function __construct(
        SaleRepository $saleRepository,
        SaleCollection $saleCollection,
        ResourceModel $resource,
        ProfileRepository $profileRepository,
        CustomerCollection $customerCollection
    ) {
        $this->saleRepository = $saleRepository;
        $this->saleCollection = $saleCollection;
        $this->resource = $resource;
        $this->profileRepository = $profileRepository;
        $this->customerCollection = $customerCollection;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $customers = [];
        foreach ($this->customerCollection as $customer) {
            $customers[$customer->getId()] = $customer;
        }
        $collection = $this->saleCollection->create();
        if (!empty($input->getArguments()['order-id'])) {
            $collection->addFieldToFilter(
                'entity_id',
                ['in' => $input->getArguments()['order-id']]
            );
        }

        if ($storeId = $input->getOption('store-id')) {
            $collection->addFieldToFilter('store_id', $storeId);
        }
        if ($fromDate = $input->getOption('from-date')) {
            $collection->addFieldToFilter('created_at', ['gteq' => $fromDate]);
        }
        if ($toDate = $input->getOption('to-date')) {
            $collection->addFieldToFilter('created_at', ['lteq' => $toDate]);
        }

        $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, $collection->getSize());
        $progressBar->setMessage('0', 'sale');
        $progressBar->setFormat(
            '<info>Sale</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%
    <info>⏺ Created:    </info>%sale%'
        );
        $output->writeln('<info>Adding sales.</info>');
        $progressBar->start();
        $progressBar->display();
        $count = 0;
        /* @var \Magento\Sales\Model\Order $order */
        foreach ($collection as $order) {
            $count += $this->saleRepository->prepareSaleData($order);
            $progressBar->setMessage((string)$count, 'sale');
            $progressBar->advance();

            if ($order->getCustomerIsGuest()) {
                $this->profileRepository->prepareGuestProfileData($order);
            } else {
                $customerId = $order->getCustomerId();
                if (array_key_exists($customerId, $customers)) {
                    $this->profileRepository->prepareProfileData($customers[$customerId]);
                } else {
                    $this->profileRepository->prepareGuestProfileData($order);
                }
            }
        }
        $progressBar->finish();
        $output->writeln('');
        return 0;
    }
}
