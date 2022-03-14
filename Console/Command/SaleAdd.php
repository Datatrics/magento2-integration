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
use Datatrics\Connect\Model\Command\SaleAdd as SaleAddProcessing;
use Datatrics\Connect\Console\CommandOptions\SaleAdd as SaleAddOptions;

/**
 * Class SaleAdd
 *
 * Prepare data for platform push
 */
class SaleAdd extends Command
{

    /**
     * Command call name
     */
    public const COMMAND_NAME = 'datatrics:sale:add';

    /**
     * @var SaleAddOptions
     */
    private $options;

    /**
     * @var SaleAddProcessing
     */
    private $saleAddProcessing;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * SalesUpdate constructor.
     * @param SaleAddOptions $options
     * @param SaleAddProcessing $saleAddProcessing
     * @param InputValidator $inputValidator
     */
    public function __construct(
        SaleAddOptions $options,
        SaleAddProcessing $saleAddProcessing,
        InputValidator $inputValidator
    ) {
        $this->options = $options;
        $this->saleAddProcessing = $saleAddProcessing;
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
This command prepare sale data to platform push
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
            $this->saleAddProcessing->run($input, $output);
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
