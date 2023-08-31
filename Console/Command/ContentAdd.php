<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Datatrics\Connect\Api\Config\System\ContentInterface as ConfigProvider;
use Datatrics\Connect\Console\CommandOptions\ContentAdd as ContentAddOptions;
use Datatrics\Connect\Console\CommandOptions\OptionKeys;
use Datatrics\Connect\Model\Command\ContentAdd as ContentAddProcessing;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    public const COMMAND_NAME = 'datatrics:content:add';

    /**
     * @var ContentAddOptions
     */
    private $options;
    /**
     * @var ContentAddProcessing
     */
    private $contentAddProcessing;
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * SalesUpdate constructor.
     * @param ConfigProvider $configProvider
     * @param ContentAddOptions $options
     * @param ContentAddProcessing $contentAddProcessing
     */
    public function __construct(
        ConfigProvider $configProvider,
        ContentAddOptions $options,
        ContentAddProcessing $contentAddProcessing
    ) {
        $this->options = $options;
        $this->contentAddProcessing = $contentAddProcessing;
        $this->configProvider = $configProvider;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Collect content to tables');
        $this->setDefinition($this->options->getOptionsList());
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $storeIds = $this->getStoreIds($input);
            $this->contentAddProcessing->run($storeIds, $output);
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

    /**
     * @param InputInterface $input
     * @return array
     */
    private function getStoreIds(InputInterface $input): array
    {
        return $input->getOption(OptionKeys::STORE_ID)
            ? [(int)$input->getOption(OptionKeys::STORE_ID)]
            : $this->configProvider->getContentEnabledStoreIds();
    }
}
