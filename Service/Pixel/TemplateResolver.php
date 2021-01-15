<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Pixel;

use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class TemplateResolver
 */
class TemplateResolver
{

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var array
     */
    private $result = [
        'success' => false,
        'data' => ''
    ];

    /**
     * TemplateResolver constructor.
     * @param LayoutInterface $layout
     */
    public function __construct(
        LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    /**
     * @param string $template
     * @return array
     */
    public function execute($template)
    {
        try {
            $this->result['data'] = $this->layout->createBlock(Template::class)
                ->setTemplate($template)
                ->toHtml();
            $this->result['success'] = true;
        } catch (\Exception $e) {
            $this->result['data'] = $e->getMessage();
        }
        return $this->result;
    }
}
