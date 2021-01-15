<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Error logger handler class
 */
class Error extends Base
{

    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * @var string
     */
    protected $fileName = '/var/log/datatrics/error.log';
}
