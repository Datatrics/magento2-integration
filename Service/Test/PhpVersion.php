<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Test;

/**
 * PHP test class
 */
class PhpVersion
{

    /**
     * Test type
     */
    public const TYPE = 'php_test';

    /**
     * Test description
     */
    public const TEST = 'Check if current PHP version is supported for this module version';

    /**
     * Visibility
     */
    public const VISIBLE = true;

    /**
     * Message on test success
     */
    public const SUCCESS_MSG = 'Version matched';

    /**
     * Message on test failed
     */
    public const FAILED_MSG = 'Minimum required PHP version: %s, current version is %s!';

    /**
     * Expected result
     */
    public const EXPECTED = '7.2';

    /**
     * Link to get support
     */
    public const SUPPORT_LINK = 'https://www.magmodules.eu/help/magento2/minimum-server-php-requirements.html';

    /**
     * @return array
     */
    public function execute()
    {
        $result = [
            'type' => self::TYPE,
            'test' => self::TEST,
            'visible' => self::VISIBLE
        ];
        if (version_compare(self::EXPECTED, PHP_VERSION) <= 0) {
            $result['result_msg'] = self::SUCCESS_MSG;
            $result +=
                [
                    'result_code' => 'success'
                ];
        } else {
            $result['result_msg'] = sprintf(
                self::FAILED_MSG,
                self::EXPECTED,
                PHP_VERSION
            );
            $result +=
                [
                    'result_code' => 'failed',
                    'support_link' => self::SUPPORT_LINK
                ];
        }
        return $result;
    }
}
