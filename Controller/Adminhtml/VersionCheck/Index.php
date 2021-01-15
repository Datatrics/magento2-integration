<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\VersionCheck;

use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Class index
 *
 * AJAX controller to check latest extension version
 */
class Index extends Action
{

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
     * Check constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigRepository $configRepository
     * @param JsonSerializer $json
     * @param File $file
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ConfigRepository $configRepository,
        JsonSerializer $json,
        File $file
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configRepository = $configRepository;
        $this->json = $json;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $result = $this->getVersions();
        $current = $latest = preg_replace('/^v/', '', $this->configRepository->getExtensionVersion());
        if ($result) {
            $data = $this->json->unserialize($result);
            $versions = array_keys($data);
            $latest = preg_replace('/^v/', '', reset($versions));
        }
        $data = [
            'current_verion' => 'v' . $current,
            'last_version' => 'v' . $latest
        ];
        return $resultJson->setData(['result' => $data]);
    }

    /**
     * @return string
     */
    private function getVersions(): string
    {
        try {
            return $this->file->fileGetContents(
                sprintf('http://version.magmodules.eu/%s.json', ConfigRepository::EXTENSION_CODE)
            );
        } catch (\Exception $exception) {
            return '';
        }
    }
}
