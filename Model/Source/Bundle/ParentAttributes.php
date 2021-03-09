<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source\Bundle;

use Datatrics\Connect\Api\Config\System\ContentInterface as ContentConfigRepository;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Attributes Option Source model
 */
class ParentAttributes implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * @var ContentConfigRepository
     */
    private $contentConfigRepository;

    /**
     * ParentAttributes constructor.
     *
     * @param ContentConfigRepository $contentConfigRepository
     */
    public function __construct(
        ContentConfigRepository $contentConfigRepository
    ) {
        $this->contentConfigRepository = $contentConfigRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = [
            ['value' => 'sku', 'label' => 'SKU'],
            ['value' => 'name', 'label' => 'Name'],
            ['value' => 'description', 'label' => 'Description'],
            ['value' => 'short_description', 'label' => 'Short Description'],
        ];
        $extraFields = $this->contentConfigRepository->getExtraFields();
        if (!$extraFields) {
            return $this->options;
        }
        foreach ($extraFields as $field) {
            $this->options[] = [
                'value' => $field['attribute'],
                'label' => $field['name']
            ];
        }
        return $this->options;
    }
}
