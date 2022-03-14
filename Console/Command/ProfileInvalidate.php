<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Datatrics\Connect\Console\CommandOptions\ProfileInvalidate as ProfileInvalidateOptions;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Command\ProfileInvalidate as ProfileInvalidateProcessing;

/**
 * Class ProfileInvalidate
 *
 * Invalidate saved profiles
 */
class ProfileInvalidate extends Command
{

    /**
     * Command call name
     */
    public const COMMAND_NAME = 'datatrics:profile:invalidate';

    /**
     * @var ProfileInvalidateProcessing
     */
    private $profileInvalidateProcessing;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * @var ProfileInvalidateOptions
     */
    private $options;

    /**
     * ProfileInvalidate constructor.
     *
     * @param ProfileInvalidateOptions $options
     * @param ProfileInvalidateProcessing $profileInvalidateProcessing
     * @param InputValidator $inputValidator
     */
    public function __construct(
        ProfileInvalidateOptions $options,
        ProfileInvalidateProcessing $profileInvalidateProcessing,
        InputValidator $inputValidator
    ) {
        $this->profileInvalidateProcessing = $profileInvalidateProcessing;
        $this->inputValidator = $inputValidator;
        $this->options = $options;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDefinition($this->options->getOptionsList());
        $this->setDescription('Invalidate profiles');
        $this->setHelp(
            <<<HELP
Invalidate profiles
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
            $timeStart = microtime(true);
            $invalidated = $this->profileInvalidateProcessing->run($input, $output);
            if (!$invalidated) {
                $output->writeln('<info>No items to invalidate</info>');
            } else {
                $output->writeln(
                    __(
                        '<info>%1 profile%2 invalidated in %3 sec.</info>',
                        $invalidated,
                        ($invalidated > 1) ? 's' : '',
                        (int)(microtime(true) - $timeStart)
                    )
                );
            }
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
