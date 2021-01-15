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
 * Class SalesUpdate
 *
 * This class contains the list options and their related constants,
 * which can be used for datatrics:sale:update CLI command
 */
class SaleUpdate extends OptionKeys
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
                InputOption::VALUE_OPTIONAL,
                'Option to push all items from store-id. If nor set, loop though all enabled store_ids.'
            ),
            new InputOption(
                self::LIMIT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Option to set a limit per run. Get default settings from config if non is set.'
            ),
            new InputOption(
                self::DRY,
                null,
                InputOption::VALUE_NONE,
                'Option to dry run data preparation.'
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
