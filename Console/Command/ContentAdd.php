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
use Datatrics\Connect\Model\Command\ContentAdd as ContentAddProcessing;
use Datatrics\Connect\Console\CommandOptions\ContentAdd as ContentAddOptions;

/**
 * Class ContentAdd
 *
 * Prepare data for platform push
 */
class ContentAdd extends Command
{

    /**
     * Command call name
     */
    const COMMAND_NAME = 'datatrics:content:add';

    /**
     * @var ContentAddOptions
     */
    private $options;

    /**
     * @var ContentAddProcessing
     */
    private $contentAddProcessing;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * SalesUpdate constructor.
     * @param ContentAddOptions $options
     * @param ContentAddProcessing $contentAddProcessing
     * @param InputValidator $inputValidator
     */
    public function __construct(
        ContentAddOptions $options,
        ContentAddProcessing $contentAddProcessing,
        InputValidator $inputValidator
    ) {
        $this->options = $options;
        $this->contentAddProcessing = $contentAddProcessing;
        $this->inputValidator = $inputValidator;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Collect content to tables');
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
            $this->contentAddProcessing->run($input, $output);
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
