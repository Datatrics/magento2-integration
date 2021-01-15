<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Product;

use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection
    as ProductCollection;

/**
 * Mass add products to datatrics queue
 */
class MassQueue extends Action
{

    /**
     * @var ContentRepository
     */
    private $contentRepository;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @param Context $context
     * @param ContentRepository $contentRepository
     * @param ProductCollection $productCollection
     */
    public function __construct(
        Context $context,
        ContentRepository $contentRepository,
        ProductCollection $productCollection
    ) {
        $this->contentRepository = $contentRepository;
        $this->productCollection = $productCollection;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $products = $this->getRequest()->getParam('selected');
        if (!$products) {
            $products = $this->productCollection->getColumnValues('entity_id');
        }
        $result = $this->contentRepository->prepareContentData($products);
        $this->messageManager->addSuccessMessage(
            __(
                '%1 product(s) added, %2 entity(s) skipped. %3 entity(s) invalidated',
                $result['added'],
                $result['skipped'],
                $result['invalidated']
            )
        );
        return $resultRedirect->setPath('catalog/product');
    }
}
