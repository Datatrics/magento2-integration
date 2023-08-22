<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Datatrics\Connect\Api\Config\System\ContentInterface as ConfigProvider;
use Datatrics\Connect\Console\CommandOptions\ContentInvalidate as ContentInvalidateOptions;
use Datatrics\Connect\Console\CommandOptions\OptionKeys;
use Datatrics\Connect\Model\Command\ContentInvalidate as ContentInvalidateProcessing;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    public const COMMAND_NAME = 'datatrics:content:invalidate';

    /**
     * @var ContentInvalidateOptions
     */
    private $options;
    /**
     * @var ContentInvalidateProcessing
     */
    private $contentInvalidateProcessing;
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param ContentInvalidateOptions $options
     * @param ContentInvalidateProcessing $contentInvalidateProcessing
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ContentInvalidateOptions $options,
        ContentInvalidateProcessing $contentInvalidateProcessing,
        ConfigProvider $configProvider
    ) {
        $this->options = $options;
        $this->contentInvalidateProcessing = $contentInvalidateProcessing;
        $this->configProvider = $configProvider;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Invalidate content');
        $this->setDefinition($this->options->getOptionsList());
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $timeStart = microtime(true);
            $invalidated = $this->contentInvalidateProcessing->run(
                $this->getStoreIds($input),
                $this->getProductIds($input)
            );

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

    /**
     * @param InputInterface $input
     * @return array|null
     */
    private function getProductIds(InputInterface $input): ?array
    {
        return $input->getArguments()[OptionKeys::PRODUCT_ID] ?? null;
    }
}
