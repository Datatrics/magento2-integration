<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Log;

use Datatrics\Connect\Logger\DebugLogger;
use Datatrics\Connect\Logger\ErrorLogger;
use Datatrics\Connect\Api\Log\RepositoryInterface
    as LogRepositoryInterface;

/**
 * Logs repository class
 */
class Repository implements LogRepositoryInterface
{

    /**
     * @var DebugLogger
     */
    private $debugLogger;

    /**
     * @var ErrorLogger
     */
    private $errorLogger;

    /**
     * Repository constructor.
     *
     * @param DebugLogger $debugLogger
     * @param ErrorLogger $errorLogger
     */
    public function __construct(
        DebugLogger $debugLogger,
        ErrorLogger $errorLogger
    ) {
        $this->debugLogger = $debugLogger;
        $this->errorLogger = $errorLogger;
    }

    /**
     * @inheritDoc
     */
    public function addErrorLog(string $type, $data)
    {
        $this->errorLogger->addLog($type, $data);
    }

    /**
     * @inheritDoc
     */
    public function addDebugLog(string $type, $data)
    {
        $this->debugLogger->addLog($type, $data);
    }
}
