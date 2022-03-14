<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Test;

use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;

/**
 * Extension status test class
 */
class ExtensionStatus
{

    /**
     * Test type
     */
    public const TYPE = 'extension_status';

    /**
     * Test description
     */
    public const TEST = 'Check if the extension is enabled in the configuration';

    /**
     * Visibility
     */
    public const VISIBLE = true;

    /**
     * Message on test success
     */
    public const SUCCESS_MSG = 'Extension is enabled';

    /**
     * Message on test failed
     */
    public const FAILED_MSG = 'Extension disabled, please enable it!';

    /**
     * Expected result
     */
    public const EXPECTED = true;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * Repository constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(
        ConfigRepository $configRepository
    ) {
        $this->configRepository = $configRepository;
    }

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

        if ($this->configRepository->isEnabled() == self::EXPECTED) {
            $result['result_msg'] = self::SUCCESS_MSG;
            $result +=
                [
                    'result_code' => 'success',
                ];
        } else {
            $result['result_msg'] = self::FAILED_MSG;
            $result +=
                [
                    'result_code' => 'failed',
                ];
        }
        return $result;
    }
}
