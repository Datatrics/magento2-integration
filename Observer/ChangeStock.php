<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Observer;

use Datatrics\Connect\Model\Content\ResourceModel as ContentResource;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;

/**
 * Class ChangeStock
 * Invalidating datatrics product after stock changed
 */
class ChangeStock implements ObserverInterface
{

    /**
     * @var ContentResource
     */
    protected $contentResource;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * InvalidateProduct constructor.
     * @param ContentResource $contentResource
     * @param LogRepository $logRepository
     */
    public function __construct(
        ContentResource $contentResource,
        LogRepository $logRepository
    ) {
        $this->contentResource = $contentResource;
        $this->logRepository = $logRepository;
    }

    /**
     * Invalidate datatrics product
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $productId = $observer->getEvent()->getItem()->getProductId();
        $connection = $this->contentResource->getConnection();
        $this->logRepository->addDebugLog('Product', 'ID ' . $productId . ' invalidated');
        $connection->update(
            $this->contentResource->getTable('datatrics_content_store'),
            ['status' => 'Queued for Update'],
            ['product_id = ?' => $productId]
        );
    }
}
