<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Test;

use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Exception;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Extension version version test class
 */
class ExtensionVersion
{

    /**
     * Test type
     */
    public const TYPE = 'extension_version';

    /**
     * Test description
     */
    public const TEST = 'Check if new extension version is available';

    /**
     * Visibility
     */
    public const VISIBLE = true;

    /**
     * Message on test success
     */
    public const SUCCESS_MSG = 'Great, you are using the latest version.';

    /**
     * Message on test failed
     */
    public const FAILED_MSG = 'Version %s is available, current version %s';

    /**
     * Expected result
     */
    public const EXPECTED = [-1, 0];

    /**
     * Link to get support
     */
    public const SUPPORT_LINK = 'https://www.magmodules.eu/help/magento2/update-extension.html';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var JsonSerializer
     */
    private $json;

    /**
     * @var File
     */
    private $file;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * ExtensionVersion constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param ConfigRepository $configRepository
     * @param LogRepository $logRepository
     * @param JsonSerializer $json
     * @param File $file
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ConfigRepository $configRepository,
        LogRepository $logRepository,
        JsonSerializer $json,
        File $file
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configRepository = $configRepository;
        $this->logRepository = $logRepository;
        $this->json = $json;
        $this->file = $file;
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
        $extensionVersion = preg_replace('/^v/', '', $this->configRepository->getExtensionVersion());
        try {
            $data = $this->file->fileGetContents(
                sprintf('http://version.magmodules.eu/%s.json', ConfigRepository::EXTENSION_CODE)
            );
        } catch (Exception $e) {
            $this->logRepository->addDebugLog('Extension version test', $e->getMessage());
            $result['result_msg'] = self::SUCCESS_MSG;
            $result +=
                [
                    'result_code' => 'success',
                ];
            return $result;
        }
        $data = $this->json->unserialize($data);
        $versions = array_keys($data);
        $latest = preg_replace('/^v/', '', reset($versions));

        if (in_array(version_compare($latest, $extensionVersion), self::EXPECTED)) {
            $result['result_msg'] = self::SUCCESS_MSG;
            $result +=
                [
                    'result_code' => 'success'
                ];
        } else {
            $result['result_msg'] = sprintf(
                self::FAILED_MSG,
                'v' . $latest,
                'v' . $extensionVersion
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
