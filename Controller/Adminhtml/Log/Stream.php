<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Log;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * AJAX controller to check logs
 */
class Stream extends Action
{

    /**
     * Error log file path pattern
     */
    public const LOG_FILE = '%s/log/datatrics-%s.log';
    /**
     * Limit stream size to 50 lines
     */
    public const MAX_LINES = 50;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var DirectoryList
     */
    private $dir;
    /**
     * @var File
     */
    private $file;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param DirectoryList $dir
     * @param File $file
     * @param DateTime $dateTime
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        DirectoryList $dir,
        File $file,
        DateTime $dateTime
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $context->getRequest();
        $this->dir = $dir;
        $this->file = $file;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * @return Json
     * @throws FileSystemException
     */
    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        $logFilePath = $this->getLogFilePath();

        if ($logFilePath && $this->isLogExists($logFilePath)) {
            $result = ['result' => $this->prepareLogText($logFilePath)];
        } else {
            $result = __('Log is empty');
        }

        return $resultJson->setData($result);
    }

    /**
     * @return string
     */
    private function getLogFilePath(): ?string
    {
        try {
            $type = $this->request->getParam('type') == 'error' ? 'error' : 'debug';
            return sprintf(self::LOG_FILE, $this->dir->getPath('var'), $type);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Check is log file exists
     *
     * @param $logFilePath
     * @return bool
     */
    private function isLogExists($logFilePath): bool
    {
        try {
            return $this->file->isExists($logFilePath);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $logFilePath
     * @return array
     * @throws FileSystemException
     */
    private function prepareLogText($logFilePath): array
    {
        $stream = $this->file->fileOpen($logFilePath, 'r');

        $this->file->fileSeek($stream, 0, SEEK_END);
        $pos = $this->file->fileTell($stream);
        $numberOfLines = self::MAX_LINES;
        while ($pos >= 0 && $numberOfLines > 0) {
            $this->file->fileSeek($stream, $pos);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $char = fgetc($stream);
            if ($char === "\n") {
                $numberOfLines--;
            }
            $pos--;
        }

        $result = [];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        while (!feof($stream) && $numberOfLines < self::MAX_LINES) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if ($line = fgets($stream)) {
                $data = explode('] ', $line);
                $date = ltrim(array_shift($data), '[');
                $data = implode('] ', $data);
                $data = explode(': ', $data);
                unset($data[0]);
                $type = $data[1] ?? '--';
                array_shift($data);

                $result[] = [
                    'date' => $this->dateTime->date('Y-m-d H:i:s', $date) . ' - ' . $type,
                    'msg' => implode(': ', $data)
                ];
            }
        }

        $this->file->fileClose($stream);
        return array_reverse($result);
    }
}
