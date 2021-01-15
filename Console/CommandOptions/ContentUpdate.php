<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\CommandOptions;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ContentUpdate
 *
 * This class contains the list options and their related constants,
 * which can be used for datatrics:content:update CLI command
 */
class ContentUpdate extends OptionKeys
{

    /**
     * Deploy static command options list
     *
     * @return array
     */
    public function getOptionsList()
    {
        return array_merge(
            $this->getBasicOptions(),
            $this->getSkipOptions()
        );
    }

    /**
     * Basic options
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getBasicOptions()
    {
        return [
            new InputOption(
                self::STORE_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Option to push all items from store-id. If nor set, loop though all enabled store_ids.'
            ),
            new InputOption(
                self::LIMIT,
                null,
                InputOption::VALUE_REQUIRED,
                'Option to limit data amount to push.'
            ),
            new InputOption(
                self::FORCE,
                null,
                InputOption::VALUE_NONE,
                'Option to avoid product status and push anyway.'
            ),
            new InputOption(
                self::DRY,
                null,
                InputOption::VALUE_NONE,
                'Option to dry run data preparation.'
            ),
            new InputArgument(
                self::PRODUCT_ID,
                InputArgument::IS_ARRAY,
                'ID of products.'
            )
        ];
    }

    /**
     * Additional options
     *
     * @return array
     */
    private function getSkipOptions()
    {
        return [];
    }
}
