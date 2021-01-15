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
 * Class ProfileInvalidate
 *
 * This class contains the list options and their related constants,
 * which can be used for datatrics:profile:invalidate CLI command
 */
class ProfileInvalidate extends OptionKeys
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
                self::CUSTOMER_ID,
                InputArgument::IS_ARRAY,
                'ID of customers.'
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
