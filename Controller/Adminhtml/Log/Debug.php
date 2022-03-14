<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Datatrics\Connect\Api\Config\RepositoryInterface
    as ConfigRepository;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

/**
 * Class debug
 *
 * AJAX controller to check debug log
 */
class Debug extends Action
{

    /**
     * Debug log file path pattern
     */
    public const DEBUG_LOG_FILE = '%s/log/datatrics/debug.log';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var DirectoryList
     */
    private $dir;

    /**
     * @var File
     */
    private $file;

    /**
     * @var SerializerJson
     */
    private $serializerJson;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigRepository $configRepository
     * @param DirectoryList $dir
     * @param File $file
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ConfigRepository $configRepository,
        DirectoryList $dir,
        File $file,
        SerializerJson $serializerJson
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configRepository = $configRepository;
        $this->dir = $dir;
        $this->file = $file;
        $this->serializerJson = $serializerJson;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if ($this->isLogExists(self::DEBUG_LOG_FILE)) {
            $result = $this->prepareLogText(self::DEBUG_LOG_FILE);
        } else {
            $result = [];
        }
        return $resultJson->setData(['result' => $result]);
    }

    /**
     * Prepare encoded log text
     *
     * @param string $file
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function prepareLogText(string $file) : array
    {
        $logFile = sprintf($file, $this->dir->getPath('var'));
        $fileContent = explode(PHP_EOL, $this->file->fileGetContents($logFile));
        if (count($fileContent) > 100) {
            $fileContent = array_slice($fileContent, -100, 100, true);
        }
        $result = [];
        foreach ($fileContent as $line) {
            $data = explode('] ', $line);
            $date = ltrim(array_shift($data), '[');
            $data = implode('] ', $data);
            $data = explode(': ', $data);
            array_shift($data);
            $result[] = [
                'date' => $date,
                'msg' => implode(': ', $data)
            ];
        }
        return $result;
    }

    /**
     * Check is log file exists
     * @param string $file
     *
     * @return bool
     */
    private function isLogExists(string $file) : bool
    {
        try {
            $logFile = sprintf($file, $this->dir->getPath('var'));
            return $this->file->isExists($logFile);
        } catch (\Exception $e) {
            return false;
        }
    }
}
