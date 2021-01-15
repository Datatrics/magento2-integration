<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Service\Product;

/**
 * Service class for attribute data
 */
class Hub
{

    /**
     * @var array
     */
    private $dataServices = [];

    /**
     * @var array
     */
    private $requiredParameters = [];

    /**
     * @var array
     */
    private $data = [
        'all' => []
    ];

    /**
     * Repository constructor.
     *
     * @param array $dataServices
     */
    public function __construct(
        $dataServices
    ) {
        $this->dataServices = $dataServices;
        foreach ($dataServices as $serviceName => $dataService) {
            $this->requiredParameters[$serviceName] = $dataService->getRequiredParameters();
            $this->data[$serviceName] = [];
        }
    }

    /**
     * @param array $servicesToRun
     * @return array
     */
    public function execute($servicesToRun = [])
    {
        $result = [];
        foreach ($this->dataServices as $serviceName => $service) {
            if (!empty($servicesToRun) && !in_array($serviceName, $servicesToRun)) {
                continue;
            }
            foreach ($service->getRequiredParameters() as $parameter) {
                if (array_key_exists($parameter, $this->data['all'])) {
                    $service->setData($parameter, $this->data['all'][$parameter]);
                } else {
                    $service->setData($parameter, $this->data[$serviceName][$parameter]);
                }
            }
            $result[$serviceName] = $service->execute();
            $service->resetData();
        }
        return $result;
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param string $target
     * @return array
     */
    public function addData($key, $data, $target = 'all')
    {
        if (!array_key_exists($target, $this->data)) {
            return ['success' => false, 'message' => __('Service %1 not provided', $target)];
        }
        $this->data[$target][$key] = $data;
        return ['success' => true, 'message' => ''];
    }
}
