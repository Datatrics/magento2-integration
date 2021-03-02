<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Plugin;

use Magento\Config\Model\Config;

/**
 * Class BeforeSetSection
 * Replace section name
 */
class BeforeSetSection
{

    /**
     * Prepare section name
     *
     * @param Config $config
     *
     * @return Config
     */
    public function beforeSave(Config $config)
    {
        if (strpos($config->getSection(), 'datatrics_connect_') !== false) {
            $config->setSection('datatrics_connect');
        }
        return $config;
    }
}
