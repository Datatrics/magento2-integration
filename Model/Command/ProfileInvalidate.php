<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Command;

use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Profile\ResourceModel as ProfileResource;

/**
 * Class ProfileInvalidate
 *
 * Invalidate profile data
 */
class ProfileInvalidate
{

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var ProfileResource
     */
    private $profileResource;

    /**
     * SalesUpdate constructor.
     * @param ProfileRepository $profileRepository
     * @param ProfileResource $profileResource
     */
    public function __construct(
        ProfileRepository $profileRepository,
        ProfileResource $profileResource
    ) {
        $this->profileRepository = $profileRepository;
        $this->profileResource = $profileResource;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->profileResource->getConnection();
        $where = [];
        if (!empty($input->getArguments()['customer-id'])) {
            $where['customer_id IN (?)'] = [$input->getArguments()['customer-id']];
        }
        return $connection->update(
            $this->contentResource->getTable('datatrics_profile'),
            ['status' => 'Queued for Update'],
            $where
        );
    }
}
