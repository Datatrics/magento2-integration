<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magmodules\AccountingBase\Console\CommandOptions\OptionKeys;

/**
 * Class ProfileUpdate
 *
 * Prepare profile data
 */
class ProfileAdd
{

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
     * @param ProfileRepository $profileRepository
     * @param CustomerCollection $customerCollection
     */
    public function __construct(
        ProfileRepository $profileRepository,
        CustomerCollection $customerCollection
    ) {
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
        $collection = $this->customerCollection->create();
        if (!empty($input->getArguments()['customer-id'])) {
            $collection->addFieldToFilter(
                'entity_id',
                ['in' => $input->getArguments()['customer-id']]
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
        $progressBar->setMessage('0', 'customer');
        $progressBar->setFormat(
            '<info>Profiles</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%
    <info>⏺ Created:    </info>%customer%'
        );
        $output->writeln('<info>Adding profiles.</info>');
        $progressBar->start();
        $progressBar->display();
        $count = 0;
        foreach ($collection as $index => $customerCollection) {
            $count += $this->profileRepository->prepareProfileData($customerCollection);
            $progressBar->setMessage((string)$count, 'customer');
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');
        return 0;
    }
}
