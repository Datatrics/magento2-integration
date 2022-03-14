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
use Datatrics\Connect\Model\Command\CategoryUpdate as CategoryUpdateProcessing;
use Datatrics\Connect\Console\CommandOptions\CategoryUpdate as CategoryUpdateOptions;

/**
 * Class CategoryUpdate
 *
 * Prepare data for platform push
 */
class CategoryUpdate extends Command
{

    /**
     * Command call name
     */
    public const COMMAND_NAME = 'datatrics:category:update';

    /**
     * @var CategoryUpdateOptions
     */
    private $options;

    /**
     * @var CategoryUpdateProcessing
     */
    private $categoryUpdateProcessing;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * SalesUpdate constructor.
     * @param CategoryUpdateOptions $options
     * @param CategoryUpdateProcessing $categoryUpdateProcessing
     * @param InputValidator $inputValidator
     */
    public function __construct(
        CategoryUpdateOptions $options,
        CategoryUpdateProcessing $categoryUpdateProcessing,
        InputValidator $inputValidator
    ) {
        $this->options = $options;
        $this->categoryUpdateProcessing = $categoryUpdateProcessing;
        $this->inputValidator = $inputValidator;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Push category to platform');
        $this->setDefinition($this->options->getOptionsList());
        $this->setHelp(
            <<<HELP
Push category to platform
HELP
        );
        parent::configure();
    }

    /**
     *  {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('store-id') === null) {
            throw new \InvalidArgumentException(
                'Please specify --store-id.'
            );
        }
        try {
            $this->categoryUpdateProcessing->run($input, $output);
            $output->writeln('<info>Categories pushed</info>');
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
