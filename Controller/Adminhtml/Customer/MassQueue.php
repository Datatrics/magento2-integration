<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection
    as CustomerCollection;
use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;

/**
 *  Mass add customers to datatrics queue
 */
class MassQueue extends Action
{

    /**
     * @var CustomerCollection
     */
    private $customerCollection;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * MassQueue constructor.
     * @param CustomerCollection $customerCollection
     * @param ProfileRepository $profileRepository
     * @param Context $context
     */
    public function __construct(
        CustomerCollection $customerCollection,
        ProfileRepository $profileRepository,
        Context $context
    ) {
        $this->customerCollection = $customerCollection;
        $this->profileRepository = $profileRepository;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $added = 0;
        $skipped = 0;
        $customers = $this->getRequest()->getParam('selected');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($customers) {
            $this->customerCollection->addFieldToFilter(
                'entity_id',
                $customers
            );
        }

        foreach ($this->customerCollection as $customer) {
            $result = $this->profileRepository->prepareProfileData($customer);
            if ($result) {
                $added++;
            } else {
                $skipped++;
            }
        }
        $this->messageManager->addSuccessMessage(
            __('%1 customer(s) added, %2 customer(s) skipped.', $added, $skipped)
        );
        return $resultRedirect->setPath('customer/index');
    }
}
