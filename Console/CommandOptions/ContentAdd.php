<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\CommandOptions;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class ContentAdd
 *
 * This class contains the list options and their related constants,
 * which can be used for datatrics:content:add CLI command
 */
class ContentAdd extends OptionKeys
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
