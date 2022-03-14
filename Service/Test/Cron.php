<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Test;

use Magento\Cron\Model\Schedule;

/**
 * Cron test class
 */
class Cron
{

    /**
     * Test type
     */
    public const TYPE = 'cron_test';

    /**
     * Test description
     */
    public const TEST = 'Check if cron is enabled and running';

    /**
     * Visibility
     */
    public const VISIBLE = true;

    /**
     * Message on test success
     */
    public const SUCCESS_MSG = 'Cron last ran at %s';

    /**
     * Message on test failed
     */
    public const FAILED_MSG = 'No active Magento cron found in the last hour!';

    /**
     * Expected result
     */
    public const EXPECTED = true;

    /**
     * Cron delay value
     */
    public const CRON_DELAY = 3600;

    /**
     * Link to get support
     */
    public const SUPPORT_LINK = 'https://www.magmodules.eu/help/magento2/cronjob-setup.html';

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * Repository constructor.
     *
     * @param Schedule $schedule
     */
    public function __construct(
        Schedule $schedule
    ) {
        $this->schedule = $schedule;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $scheduledAt = '';
        $result = [
            'type' => self::TYPE,
            'test' => self::TEST,
            'visible' => self::VISIBLE,

        ];
        $scheduleCollection = $this->schedule->getCollection()
            ->addFieldToSelect('scheduled_at')
            ->addFieldToFilter('status', 'success');

        $scheduleCollection->getSelect()
            ->limit(1)
            ->order('scheduled_at DESC');
        if ($scheduleCollection->getSize() == 0) {
            $cronStatus = false;
        } else {
            $scheduledAt = $scheduleCollection->getFirstItem()->getScheduledAt();
            $cronStatus = (time() - strtotime($scheduledAt)) < self::CRON_DELAY;
        }
        if ($cronStatus == self::EXPECTED) {
            $result['result_msg'] = sprintf(self::SUCCESS_MSG, $scheduledAt);
            $result +=
                [
                    'result_code' => 'success'
                ];
        } else {
            $result['result_msg'] = self::FAILED_MSG;
            $result +=
                [
                    'result_code' => 'failed',
                    'support_link' => self::SUPPORT_LINK
                ];
        }
        return $result;
    }
}
