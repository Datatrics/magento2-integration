<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Selftest\Repository;

/**
 * Class Selftest
 *
 * Perform tests on module
 */
class Selftest extends Command
{

    /**
     * Command call name
     */
    const COMMAND_NAME = 'datatrics:selftest';

    /**
     * @var Repository
     */
    private $selftestRepository;

    /**
     * Selftest constructor.
     *
     * @param Repository $selftestRepository
     */
    public function __construct(
        Repository $selftestRepository
    ) {
        $this->selftestRepository = $selftestRepository;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Perform self test of extension');
        parent::configure();
    }

    /**
     *  {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->selftestRepository->test();
        foreach ($result as $test) {
            if ($test['result_code'] == 'success') {
                $output->writeln(
                    sprintf('<info>%s:</info> %s
    - %s', $test['test'], $test['result_code'], $test['result_msg'])
                );
            } else {
                $output->writeln(
                    sprintf('<info>%s:</info> <error>%s</error>
    - %s', $test['test'], $test['result_code'], $test['result_msg'])
                );
            }
        }
        return 0;
    }
}
