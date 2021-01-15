<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Datatrics\Connect\Console\CommandOptions\SaleUpdate as SaleUpdateOptions;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Model\Command\SaleUpdate as SaleUpdateProcessing;

/**
 * Class SaleUpdate
 *
 * Push updated data to the platform
 */
class SaleUpdate extends Command
{

    /**
     * Command call name
     */
    const COMMAND_NAME = 'datatrics:sale:update';

    /**
     * @var SaleUpdateOptions
     */
    private $options;

    /**
     * @var SaleUpdateProcessing
     */
    private $saleUpdateProcessing;

    /**
     * SalesUpdate constructor.
     * @param SaleUpdateOptions $options
     * @param SaleUpdateProcessing $saleUpdateProcessing
     */
    public function __construct(
        SaleUpdateOptions $options,
        SaleUpdateProcessing $saleUpdateProcessing
    ) {
        $this->options = $options;
        $this->saleUpdateProcessing = $saleUpdateProcessing;
        parent::__construct();
    }

    /**
     *  {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Push sales to the platform');
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
        $this->saleUpdateProcessing->run($input, $output);
        $output->writeln('<info>Done</info>');
        return Cli::RETURN_SUCCESS;
    }
}
