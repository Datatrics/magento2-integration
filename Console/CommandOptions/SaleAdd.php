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
 * Class SaleAdd
 *
 * This class contains the list options and their related constants,
 * which can be used for datatrics:sale:add CLI command
 */
class SaleAdd extends OptionKeys
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
                self::FROM_DATE,
                null,
                InputOption::VALUE_REQUIRED,
                'Start interval of order sync date. This param is required if --to-date was input.'
            ),
            new InputOption(
                self::TO_DATE,
                null,
                InputOption::VALUE_REQUIRED,
                'End interval of order sync date. If this param is empty - system, will use current date.'
            ),
            new InputOption(
                self::STORE_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Option to push all items from store-id. If nor set, loop though all enabled store_ids.'
            ),
            new InputArgument(
                self::CUSTOMER_ID,
                InputArgument::IS_ARRAY,
                'ID of order.'
            ),
            new InputOption(
                self::OFFSET,
                null,
                InputOption::VALUE_REQUIRED,
                'Sync order with days offset from today.'
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
