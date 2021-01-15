<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Test\Integration\Model\Test;

use Magento\Framework\App\ObjectManager;
use Datatrics\Connect\Api\Config\RepositoryInterface;
use Datatrics\Connect\Service\Test\ExtensionStatus;
use PHPUnit\Framework\TestCase;

class ExtensionStatusTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testReturnsErrorWhenTheModuleIsDisabled()
    {
        $configRepositoryMock = $this->createMock(RepositoryInterface::class);
        $configRepositoryMock->method('isEnabled')->willReturn(false);

        /** @var ExtensionStatus $instance */
        $instance = $this->objectManager->create(ExtensionStatus::class, [
            'configRepository' => $configRepositoryMock,
        ]);

        $result = $instance->execute();

        $this->assertEquals(ExtensionStatus::FAILED_MSG, $result['result_msg']);
    }

    public function testReturnsSuccessWhenTheModuleIsEnabled()
    {
        $configRepositoryMock = $this->createMock(RepositoryInterface::class);
        $configRepositoryMock->method('isEnabled')->willReturn(true);

        /** @var ExtensionStatus $instance */
        $instance = $this->objectManager->create(ExtensionStatus::class, [
            'configRepository' => $configRepositoryMock,
        ]);

        $result = $instance->execute();

        $this->assertEquals(ExtensionStatus::SUCCESS_MSG, $result['result_msg']);
    }
}
