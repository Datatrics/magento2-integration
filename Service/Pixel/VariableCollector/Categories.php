<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Pixel\VariableCollector;

use Magento\Catalog\Helper\Data;

/**
 * Class Categories
 */
class Categories
{

    /**
     * @var Data
     */
    private $data;

    /**
     * Categories constructor.
     *
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $category = $this->data->getCategory();
        return [
            'categoryname' => $category->getName()
        ];
    }
}
