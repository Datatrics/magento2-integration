<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Datatrics\Connect\Console\CommandOptions\ProfileUpdate as ProfileUpdateOptions;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Command\ProfileUpdate as ProfileUpdateProcessing;

/**
 * Class ProfileUpdate
 *
 * Push updated data to the platform
 */
class ProfileUpdate extends Command
{

    /**
     * Command call name
     */
    const COMMAND_NAME = 'datatrics:profile:update';

    /**
     * @var ProfileUpdateOptions
     */
    private $options;

    /**
     * @var ProfileUpdateProcessing
     */
    private $profileUpdateProcessing;

    /**
     * SalesUpdate constructor.
     * @param ProfileUpdateOptions $options
     * @param ProfileUpdateProcessing $profileUpdateProcessing
     */
    public function __construct(
        ProfileUpdateOptions $options,
        ProfileUpdateProcessing $profileUpdateProcessing
    ) {
        $this->options = $options;
        $this->profileUpdateProcessing = $profileUpdateProcessing;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Push profiles to the platform');
        $this->setDefinition($this->options->getOptionsList());
        $this->setHelp(
            <<<HELP
This command push updated to the platform
HELP
        );
        parent::configure();
    }

    /**
     *  {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->profileUpdateProcessing->run($input, $output);
        $output->writeln('<info>Done</info>');
        return Cli::RETURN_SUCCESS;
    }
}
