<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Api\API\AdapterInterface as ApiAdapter;
use Datatrics\Connect\Model\Profile\CollectionFactory as ProfileCollectionFactory;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ProfileUpdate
 *
 * Prepare profile data
 */
class ProfileUpdate
{

    /**
     * @var ProfileCollectionFactory
     */
    private $profileCollectionFactory;

    /**
     * @var ApiAdapter
     */
    private $apiAdapter;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var Json
     */
    private $json;

    private $isDry = false;

    /**
     * ProfileUpdate constructor.
     * @param ProfileCollectionFactory $profileCollectionFactory
     * @param ApiAdapter $apiAdapter
     * @param ConfigRepository $configRepository
     * @param Json $json
     */
    public function __construct(
        ProfileCollectionFactory $profileCollectionFactory,
        ApiAdapter $apiAdapter,
        ConfigRepository $configRepository,
        Json $json
    ) {
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->apiAdapter = $apiAdapter;
        $this->configRepository = $configRepository;
        $this->json = $json;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->isDry = (bool)$input->getOption('dry');
        $collection = $this->profileCollectionFactory->create()
            ->addFieldToFilter('status', ['neq' => 'Synced']);
        if ($storeId = $input->getOption('store-id')) {
            $collection->addFieldToFilter('store_id', $storeId);
        }

        if ($limit = $input->getOption('limit')) {
            $collection->setPageSize($limit)->setCurPage(1);
        }
        if (!$limit) {
            $limit = $collection->getSize();
        }
        $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output, (int)$limit);
        $progressBar->setMessage('0', 'customer');
        $progressBar->setFormat(
            '<info>Profiles</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%
    <info>⏺ Pushed:    </info>%customer%'
        );
        $count = 0;
        $output->writeln('<info>Pushing profiles.</info>');
        $progressBar->start();
        $progressBar->display();
        foreach ($collection as $profile) {
            if ($this->isDry) {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                print_r($this->prepareData($profile));
                continue;
            }
            $response = $this->apiAdapter->execute(
                ApiAdapter::CREATE_PROFILE,
                null,
                $this->json->serialize($this->prepareData($profile))
            );
            if ($response['success']) {
                $profile->setStatus('Synced')->save();
            } else {
                $profile->setStatus('Error')->save();
                $profile->setUpdateAttempts($profile->getUpdateAttempts() + 1)->save();
            }
            $count += (int)$response['success'];
            $progressBar->setMessage((string)$count, 'customer');
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');
        return 0;
    }

    /**
     * @param \Datatrics\Connect\Model\Profile\Data $profile
     * @return array
     */
    private function prepareData($profile)
    {
        return [
            "projectid" => $this->configRepository->getProjectId(),
            "profileid" => $profile->getProfileId(),
            "objecttype" => "profile",
            "source" => 'Magento2',
            "profile" => $profile->getData()
        ];
    }
}
