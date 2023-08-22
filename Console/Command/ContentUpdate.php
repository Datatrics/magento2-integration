<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Datatrics\Connect\Console\CommandOptions\ContentUpdate as ContentUpdateOptions;
use Datatrics\Connect\Model\Command\ContentUpdate as ContentUpdateProcessing;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ContentUpdate
 *
 * Prepare data for platform push
 */
class ContentUpdate extends Command
{

    /**
     * Command call name
     */
    public const COMMAND_NAME = 'datatrics:content:update';

    /**
     * @var ContentUpdateOptions
     */
    private $options;
    /**
     * @var ContentUpdateProcessing
     */
    private $contentUpdateProcessing;

    /**
     * ContentUpdate constructor.
     * @param ContentUpdateOptions $options
     * @param ContentUpdateProcessing $contentUpdateProcessing
     */
    public function __construct(
        ContentUpdateOptions $options,
        ContentUpdateProcessing $contentUpdateProcessing
    ) {
        $this->options = $options;
        $this->contentUpdateProcessing = $contentUpdateProcessing;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Push content to platform');
        $this->setDefinition($this->options->getOptionsList());
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('store-id') === null) {
            throw new \InvalidArgumentException(
                'Please specify --store-id.'
            );
        }
        try {
            $this->contentUpdateProcessing->run($input, $output);
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
