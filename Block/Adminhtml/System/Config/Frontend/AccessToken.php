<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\System\Config\Frontend;

use Datatrics\Connect\Service\Integration\Create as CreateToken;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;

class AccessToken extends Field
{
    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;
    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * @param Context $context
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     * @param array $data
     */
    public function __construct(
        Context $context,
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService,
        array $data = []
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setData('readonly', 1);
        $element->setData('value', $this->getToken());
        return $element->getElementHtml();
    }

    /**
     * @return string
     */
    private function getToken(): string
    {
        $integration = $this->integrationService->findByName(CreateToken::INTEGRATION_NAME);
        if ($integration->getId()) {
            $customerId = $integration->getConsumerId();
            return $this->oauthService->getAccessToken($customerId)->getToken();
        }

        return '';
    }
}
