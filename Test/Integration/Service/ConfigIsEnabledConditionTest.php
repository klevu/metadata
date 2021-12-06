<?php

namespace Klevu\Metadata\Test\Integration\Service;

use Klevu\Metadata\Service\IsEnabledCondition\ConfigIsEnabledCondition;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConfigIsEnabledConditionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture default_store klevu_search/metadata/enabled 1
     */
    public function testExecuteWhenEnabled()
    {
        $this->setupPhp5();

        /** @var ConfigIsEnabledCondition $configIsEnabledCondition */
        $configIsEnabledCondition = $this->objectManager->get(ConfigIsEnabledCondition::class);

        $this->assertTrue($configIsEnabledCondition->execute(1));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 0
     * @magentoConfigFixture default_store klevu_search/metadata/enabled 0
     */
    public function testExecuteWhenDisabled()
    {
        $this->setupPhp5();

        /** @var ConfigIsEnabledCondition $configIsEnabledCondition */
        $configIsEnabledCondition = $this->objectManager->get(ConfigIsEnabledCondition::class);

        $this->assertFalse($configIsEnabledCondition->execute(1));
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }
}
