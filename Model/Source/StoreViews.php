<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Stores Option Source
 */
class StoreViews implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Stores constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Returns array of availabe stores
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $stores = $this->storeManager->getStores();
            foreach ($stores as $store) {
                $this->options[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName() . ' (' . $store->getCode() . ')'
                ];
            }
        }
        return $this->options;
    }
}
