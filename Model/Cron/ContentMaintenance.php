<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Cron;

use Datatrics\Connect\Api\Config\System\ContentInterface as ConfigProvider;
use Datatrics\Connect\Model\Command\ContentAdd;

/**
 * Class ContentMaintenance
 *
 * Prepare content data
 */
class ContentMaintenance
{

    public $storeIds = [];

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var ContentAdd
     */
    private $contentAdd;

    /**
     * @param ConfigProvider $configProvider
     * @param ContentAdd $contentAdd
     */
    public function __construct(
        ConfigProvider $configProvider,
        ContentAdd $contentAdd
    ) {
        $this->configProvider = $configProvider;
        $this->contentAdd = $contentAdd;
    }

    /**
     * Schedule products to delete and add
     *
     * @return $this
     */
    public function execute(): ContentMaintenance
    {
        if (!$this->configProvider->isEnabled()) {
            return $this;
        }

        $storeIds = $this->getStoreIds();
        foreach ($storeIds as $storeId) {
            $this->contentAdd->addProducts($storeId);
        }

        return $this;
    }

    /**
     * @return array
     */
    private function getStoreIds(): array
    {
        if (!$this->storeIds) {
            $this->storeIds = $this->configProvider->getContentEnabledStoreIds();
        }

        return $this->storeIds;
    }
}
