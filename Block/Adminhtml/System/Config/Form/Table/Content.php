<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\System\Config\Form\Table;

use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Content Table Block for system config
 */
class Content extends Template implements RendererInterface
{

    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Datatrics_Connect::system/config/fieldset/table/content.phtml';

    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var ContentResource
     */
    private $contentResource;

    /**
     * Content constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ContentConfigRepository $contentConfigRepository
     * @param LogRepository $logRepository
     * @param ContentResource $contentResource
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ContentConfigRepository $contentConfigRepository,
        LogRepository $logRepository,
        ContentResource $contentResource
    ) {
        $this->storeManager = $storeManager;
        $this->contentConfigRepository = $contentConfigRepository;
        $this->logRepository = $logRepository;
        $this->contentResource = $contentResource;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $this->setData('element', $element);
        return $this->toHtml();
    }

    /**
     * Returns content configuration data array for all stores
     *
     * @return array
     */
    public function getContentStoreData(): array
    {
        $storeData = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = (int)$store->getStoreId();
            try {
                $storeData[$storeId] = [
                    'store_id' => $storeId,
                    'code' => $store->getCode(),
                    'name' => $store->getName(),
                    'is_active' => $store->getIsActive() ? 'Enabled' : 'Disabled',
                    'status' => $this->contentConfigRepository->isEnabled($storeId) ? 'Enabled' : 'Disabled',
                    'project_id' => $this->contentConfigRepository->getProjectId($storeId),
                    'source' => $this->contentConfigRepository->getSyncSource($storeId),
                    'totals' => $this->getContentData($storeId),
                    'content_add_url' => $this->getUrl('datatrics/content/add', ['store_id' => $storeId]),
                    'content_invalidate_url' => $this->getUrl(
                        'datatrics/content/invalidate',
                        ['store_id' => $storeId]
                    ),
                    'content_update_url' => $this->getUrl(
                        'datatrics/content/update',
                        ['store_id' => $storeId]
                    )
                ];
            } catch (\Exception $e) {
                $this->logRepository->addErrorLog('LocalizedException', $e->getMessage());
                continue;
            }
        }

        return $storeData;
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getContentData(int $storeId): array
    {
        $totals = [];
        $connection = $this->contentResource->getConnection();

        $statuses = [
            'items' => null,
            'invalidated' => 'Queued for Update',
            'synced' => 'Synced',
            'skipped' => 'Skipped'
        ];

        foreach ($statuses as $key => $status) {
            $selectContent = $connection->select()->from(
                $this->contentResource->getTable('datatrics_content_store'),
                'product_id'
            )->where('store_id = ?', $storeId);
            if ($status !== null) {
                $selectContent->where('status = ?', $status);
            }
            $totals[$key] = count($connection->fetchAll($selectContent));
        }

        return $totals;
    }
}
