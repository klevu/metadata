<?php

namespace Klevu\Metadata\Test\Integration\Service;

use Klevu\Metadata\Service\IsEnabledDeterminer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class IsEnabledDeterminerTest extends TestCase
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
    public function testExecuteWhenConfigEnabled()
    {
        $this->setupPhp5();

        /** @var IsEnabledDeterminer $isEnabledDeterminer */
        $isEnabledDeterminer = $this->objectManager->get(IsEnabledDeterminer::class);

        $this->assertTrue($isEnabledDeterminer->execute(1));
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
