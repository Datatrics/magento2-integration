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
 * Class ProfileAdd
 *
 * This class contains the list options and their related constants,
 * which can be used for datatrics:content:invalidate CLI command
 */
class ContentInvalidate extends OptionKeys
{

    /**
     * Deploy static command options list
     *
     * @return array
     */
    public function getOptionsList(): array
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
    private function getBasicOptions(): array
    {
        return [
            new InputOption(
                self::STORE_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Option to invalidate all items from store-id. If nor set, loop though all store_ids.'
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
    private function getSkipOptions(): array
    {
        return [];
    }
}
