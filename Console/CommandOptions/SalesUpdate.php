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
class SalesUpdate extends OptionKeys
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
            new InputArgument(
                self::STORE_ID,
                InputArgument::IS_ARRAY,
                'Option to push all items from store-_id. If nor set, loop though all enabled store_ids.'
            ),
            new InputOption(
                self::LIMIT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Option to set a limit per run. Get default settings from config if non is set.'
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
