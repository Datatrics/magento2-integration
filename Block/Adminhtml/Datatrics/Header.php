<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Block\Adminhtml\Datatrics;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;

/**
 * System Configration Module information Block
 */
class Header extends Field
{

    public const MODULE_SUPPORT_LINK = 'https://www.magmodules.eu/help/%s';
    public const MODULE_CONTACT_LINK = 'https://www.magmodules.eu/support.html?ext=%s';

    /**
     * @var string
     */
    protected $_template = 'Datatrics_Connect::system/config/fieldset/header.phtml';

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * Header constructor.
     *
     * @param Context $context
     * @param ConfigRepository $config
     */
    public function __construct(
        Context $context,
        ConfigRepository $config
    ) {
        $this->configRepository = $config;
        parent::__construct($context);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element) : string
    {
        $element->addClass('datatrics');

        return $this->toHtml();
    }

    /**
     * Image with extension and magento version.
     *
     * @return string
     */
    public function getImage(): string
    {
        return sprintf(
            'https://www.magmodules.eu/logo/%s/%s/%s/logo.png',
            $this->configRepository->getExtensionCode(),
            $this->configRepository->getExtensionVersion(),
            $this->configRepository->getMagentoVersion()
        );
    }

    /**
     * Contact link for extension.
     *
     * @return string
     */
    public function getContactLink(): string
    {
        return sprintf(
            self::MODULE_CONTACT_LINK,
            $this->configRepository->getExtensionCode()
        );
    }

    /**
     * Support link for extension.
     *
     * @return string
     */
    public function getSupportLink(): string
    {
        return sprintf(
            self::MODULE_SUPPORT_LINK,
            $this->configRepository->getExtensionCode()
        );
    }
}
