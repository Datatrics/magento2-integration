<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Pixel\VariableCollector;

use Datatrics\Connect\Api\Config\RepositoryInterface
    as ConfigRepository;

/**
 * Class Base
 */
class Base
{

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * Base constructor.
     * @param ConfigRepository $configRepository
     */
    public function __construct(
        ConfigRepository $configRepository
    ) {
        $this->configRepository = $configRepository;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            'project_id' => $this->configRepository->getProjectId()
        ];
    }
}
