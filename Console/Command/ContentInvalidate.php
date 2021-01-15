<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Command\ContentInvalidate as ContentInvalidateProcessing;
use Datatrics\Connect\Console\CommandOptions\ContentInvalidate as ContentInvalidateOptions;

/**
 * Class ContentInvalidate
 *
 * Invalidate saved content
 */
class ContentInvalidate extends Command
{

    /**
     * Command call name
     */
    const COMMAND_NAME = 'datatrics:content:invalidate';

    /**
     * @var ContentInvalidateOptions
     */
    private $options;

    /**
     * @var ContentInvalidateProcessing
     */
    private $contentInvalidateProcessing;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * SalesUpdate constructor.
     * @param ContentInvalidateOptions $options
     * @param ContentInvalidateProcessing $contentInvalidateProcessing
     * @param InputValidator $inputValidator
     */
    public function __construct(
        ContentInvalidateOptions $options,
        ContentInvalidateProcessing $contentInvalidateProcessing,
        InputValidator $inputValidator
    ) {
        $this->options = $options;
        $this->contentInvalidateProcessing = $contentInvalidateProcessing;
        $this->inputValidator = $inputValidator;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Invalidate content');
        $this->setDefinition($this->options->getOptionsList());
        $this->setHelp(
            <<<HELP
Collect content to tables
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
            $invalidated = $this->contentInvalidateProcessing->run($input, $output);
            if (!$invalidated) {
                $output->writeln('<info>No items to invalidate</info>');
            } else {
                $output->writeln(
                    __(
                        '<info>%1 item%2 invalidated in %3 sec.</info>',
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
