<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Datatrics\Connect\Console\CommandOptions\ProfileAdd as ProfileAddOptions;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Command\ProfileAdd as ProfileAddProcessing;

/**
 * Class ProfileAdd
 *
 * Prepare data for platform push
 */
class ProfileAdd extends Command
{

    /**
     * Command call name
     */
    public const COMMAND_NAME = 'datatrics:profile:add';

    /**
     * @var ProfileAddOptions
     */
    private $options;

    /**
     * @var ProfileAddProcessing
     */
    private $profileAddProcessing;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * SalesUpdate constructor.
     * @param ProfileAddOptions $options
     * @param ProfileAddProcessing $profileAddProcessing
     * @param InputValidator $inputValidator
     */
    public function __construct(
        ProfileAddOptions $options,
        ProfileAddProcessing $profileAddProcessing,
        InputValidator $inputValidator
    ) {
        $this->options = $options;
        $this->profileAddProcessing = $profileAddProcessing;
        $this->inputValidator = $inputValidator;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Push updated to the platform');
        $this->setDefinition($this->options->getOptionsList());
        $this->setHelp(
            <<<HELP
This command prepare profile data to platform push
HELP
        );
        parent::configure();
    }

    /**
     *  {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->inputValidator->validate($input);
            $this->profileAddProcessing->run($input, $output);
            $output->writeln('<info>Done</info>');
        } catch (\InvalidArgumentException $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        } catch (\Exception $exception) {
            $output->writeln('<info>' . $exception->getMessage() . '</info>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
