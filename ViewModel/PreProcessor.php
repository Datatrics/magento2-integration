<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Datatrics\Connect\Service\Pixel\TemplatePreparator;
use Datatrics\Connect\Service\Pixel\TemplateResolver;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;

/**
 * PreProcessor data class
 */
class PreProcessor implements ArgumentInterface
{

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
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * PreProcessor constructor.
     * @param TemplatePreparator $templatePreparator
     * @param TemplateResolver $templateResolver
     * @param ConfigRepository $configRepository
     * @param mixed $variableProcessors
     */
    public function __construct(
        TemplatePreparator $templatePreparator,
        TemplateResolver $templateResolver,
        ConfigRepository $configRepository,
        $variableProcessors
    ) {
        $this->templatePreparator = $templatePreparator;
        $this->templateResolver = $templateResolver;
        $this->variableProcessors = $variableProcessors;
        $this->configRepository = $configRepository;
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
        if (!$this->configRepository->isTrackingEnabled()) {
            return '';
        }
        $html = $this->templateResolver->execute($template)['data'];
        if (!$html) {
            return 'error';
        }
        $variables = $this->variableProcessors[$variableProcessor]->execute();
        return $this->templatePreparator->execute($html, $variables);
    }
}
