<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\System\Config\Form\Table;

use Datatrics\Connect\Api\Config\System\ProfileInterface as ProfileConfigRepository;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Profile Table Block for system config
 */
class Profile extends Template implements RendererInterface
{

    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Datatrics_Connect::system/config/fieldset/table/profile.phtml';

    /**
     * @var ProfileConfigRepository
     */
    private $profileConfigRepository;
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
     * @param ProfileConfigRepository $profileConfigRepository
     * @param LogRepository $logRepository
     * @param ContentResource $contentResource
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProfileConfigRepository $profileConfigRepository,
        LogRepository $logRepository,
        ContentResource $contentResource
    ) {
        $this->storeManager = $storeManager;
        $this->profileConfigRepository = $profileConfigRepository;
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
     * Returns profile configuration data array for all stores
     *
     * @return array
     */
    public function getProfileStoreData(): array
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
                    'status' => $this->profileConfigRepository->isEnabled($storeId) ? 'Enabled' : 'Disabled',
                    'project_id' => $this->profileConfigRepository->getProjectId($storeId),
                    'source' => $this->profileConfigRepository->getSyncSource($storeId),
                    'totals' => $this->getProfileData($storeId),
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
    private function getProfileData(int $storeId): array
    {
        $totals = [];
        $connection = $this->contentResource->getConnection();
        $selectContent = $connection->select()->from(
            $this->contentResource->getTable('datatrics_profile'),
            'entity_id'
        )->where('store_id = ?', $storeId);
        $totals['customers'] = count($connection->fetchAll($selectContent));
        $selectContent->where('status = ?', 'Queued for Update');
        $totals['queued'] = count($connection->fetchAll($selectContent));
        return $totals;
    }
}
