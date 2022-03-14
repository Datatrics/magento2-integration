<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\ViewModel;

use Datatrics\Connect\Api\Config\System\TrackingInterface as TrackingConfigRepository;
use Datatrics\Connect\Service\Pixel\TemplatePreparator;
use Datatrics\Connect\Service\Pixel\TemplateResolver;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;

/**
 * PreProcessor data class
 */
class PreProcessor implements ArgumentInterface
{
    /**
     *
     */
    public const URL_PATH = 'datatrics/cart/get';

    /**
     * @var TemplatePreparator
     */
    private $templatePreparator;

    /**
     * @var TemplateResolver
     */
    private $templateResolver;

    /**
     * @var array
     */
    private $variableProcessors;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var TrackingConfigRepository
     */
    private $trackingConfigRepository;

    /**
     * PreProcessor constructor.
     * @param TemplatePreparator $templatePreparator
     * @param TemplateResolver $templateResolver
     * @param TrackingConfigRepository $trackingConfigRepository
     * @param UrlInterface $urlBuilder
     * @param mixed $variableProcessors
     */
    public function __construct(
        TemplatePreparator $templatePreparator,
        TemplateResolver $templateResolver,
        TrackingConfigRepository $trackingConfigRepository,
        UrlInterface $urlBuilder,
        $variableProcessors
    ) {
        $this->templatePreparator = $templatePreparator;
        $this->templateResolver = $templateResolver;
        $this->variableProcessors = $variableProcessors;
        $this->urlBuilder = $urlBuilder;
        $this->trackingConfigRepository = $trackingConfigRepository;
    }

    /**
     * @param string $template
     * @param mixed $variableProcessor
     * @return string
     */
    public function getTrack(
        $template,
        $variableProcessor
    ): string {
        if (!$this->trackingConfigRepository->isEnabled()) {
            return '';
        }
        $html = $this->templateResolver->execute($template)['data'];
        if (!$html) {
            return 'error';
        }
        $variables = $this->variableProcessors[$variableProcessor]->execute();
        return $this->templatePreparator->execute($html, $variables);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->urlBuilder->getUrl(self::URL_PATH);
    }
}
