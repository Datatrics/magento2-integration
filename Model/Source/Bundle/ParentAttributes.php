<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source\Bundle;

use Magento\Framework\Data\OptionSourceInterface;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\Serialize\Serializer\Json;

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
     * @var Json
     */
    private $json;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * ParentAttributes constructor.
     *
     * @param ConfigRepository $configRepository
     * @param Json $json
     */
    public function __construct(
        ConfigRepository $configRepository,
        Json $json
    ) {
        $this->configRepository = $configRepository;
        $this->json = $json;
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
        $extraFields = $this->configRepository->getExtraFields();
        if (!$extraFields) {
            return $this->options;
        }
        $extraFields = $this->json->unserialize(
            $extraFields
        );
        foreach ($extraFields as $field) {
            $this->options[] = [
                'value' => $field['attribute'],
                'label' => $field['name']
            ];
        }
        return $this->options;
    }
}
