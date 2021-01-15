<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Console\Command;

use Magento\Framework\Stdlib\DateTime;
use Symfony\Component\Console\Input\InputInterface;
use Datatrics\Connect\Console\CommandOptions\OptionKeys as Options;

/**
 * Command input arguments validator class
 */
class InputValidator
{

    /**
     * @param InputInterface $input
     * @throws \Exception
     */
    public function validate(InputInterface $input)
    {
        if ($input->getOption(Options::FROM_DATE) || $input->getOption(Options::TO_DATE)) {
            $this->checkDatesInput(
                $input->getOption(Options::FROM_DATE),
                $input->getOption(Options::TO_DATE),
                $input->getOption(Options::STORE_ID)
            );
        }
    }

    /**
     * Validate options related to date
     *
     * @param string|null $fromDate
     * @param string|null $toDate
     * @param string|null $storeID
     * @return void
     * @throws \InvalidArgumentException
     */
    private function checkDatesInput(
        string $fromDate = null,
        string $toDate = null,
        string $storeID = null
    ) {
        if (!$storeID) {
            throw new \InvalidArgumentException(
                'Param --to-date and --from-date depends to --store-id. If you input dates you must input --store-id.'
            );
        }
        if ($fromDate === null && $toDate !== null) {
            throw new \InvalidArgumentException(
                'Param --to-date depend on --from-date. If you input --to-date you must input --from-date.'
            );
        }
        if ($toDate == null) {
            $toDate = date('Y-m-d', strtotime('Now'));
        }
        if ((false === \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $fromDate)
                && false === \DateTime::createFromFormat(DateTime::DATE_PHP_FORMAT, $fromDate))
            || (false === \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $toDate)
                && false === \DateTime::createFromFormat(DateTime::DATE_PHP_FORMAT, $toDate))
        ) {
            throw new \InvalidArgumentException(
                'Perhaps --from-date and|or --to-date have incorrect format.' . PHP_EOL
                . 'They should look like "yyyy-mm-dd" or "yyyy-mm-dd hh:mm:ss"'
            );
        }
    }
}
