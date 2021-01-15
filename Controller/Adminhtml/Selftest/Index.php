<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Controller\Adminhtml\Selftest;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Datatrics\Connect\Api\Selftest\RepositoryInterface
    as SelftestRepository;

/**
 * Class index
 *
 * AJAX controller to is provided check result
 */
class Index extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SelftestRepository
     */
    private $selftestRepository;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SelftestRepository $selftestRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        SelftestRepository $selftestRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->selftestRepository = $selftestRepository;
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $result = $this->selftestRepository->test();
        return $resultJson->setData(['result' => $result]);
    }
}
