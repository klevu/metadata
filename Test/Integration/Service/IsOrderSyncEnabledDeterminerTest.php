<?php

namespace Klevu\Metadata\Test\Integration\Service;

use Klevu\Metadata\Service\IsEnabledDeterminer;
use Klevu\Metadata\Service\IsOrderSyncEnabledDeterminer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class IsOrderSyncEnabledDeterminerTest extends TestCase
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
     * @magentoDataFixture loadWebsiteFixtures
     * @magentoConfigFixture default/klevu_search/metadata/enabled 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     */
    public function testExecuteWhenConfigEnabledAtStoreLevel()
    {
        $this->setupPhp5();

        $store = $this->getStore('klevu_test_store_1');

        /** @var IsOrderSyncEnabledDeterminer $isEnabledDeterminer */
        $isEnabledDeterminer = $this->objectManager->get(IsOrderSyncEnabledDeterminer::class);

        $this->assertTrue($isEnabledDeterminer->execute($store->getId()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadWebsiteFixtures
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 0
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 0
     */
    public function testExecuteWhenConfigDisabledAtStoreLevel()
    {
        $this->setupPhp5();

        $store = $this->getStore('klevu_test_store_1');

        /** @var IsOrderSyncEnabledDeterminer $isEnabledDeterminer */
        $isEnabledDeterminer = $this->objectManager->get(IsOrderSyncEnabledDeterminer::class);

        $this->assertFalse($isEnabledDeterminer->execute($store->getId()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadWebsiteFixtures
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 0
     */
    public function testExecuteWhenMetaDataEnabledAndSyncDisabled()
    {
        $this->setupPhp5();

        $store = $this->getStore('klevu_test_store_1');

        /** @var IsOrderSyncEnabledDeterminer $isEnabledDeterminer */
        $isEnabledDeterminer = $this->objectManager->get(IsOrderSyncEnabledDeterminer::class);

        $this->assertFalse($isEnabledDeterminer->execute($store->getId()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadWebsiteFixtures
     * @magentoConfigFixture default/klevu_search/metadata/enabled 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 0
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     */
    public function testExecuteWhenMetaDataDisabledAndSyncEnabled()
    {
        $this->setupPhp5();

        $store = $this->getStore('klevu_test_store_1');

        /** @var IsOrderSyncEnabledDeterminer $isEnabledDeterminer */
        $isEnabledDeterminer = $this->objectManager->get(IsOrderSyncEnabledDeterminer::class);

        $this->assertFalse($isEnabledDeterminer->execute($store->getId()));
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @param string $storeCode
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore($storeCode)
    {
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);

        return $storeRepository->get($storeCode);
    }

    /**
     * Loads website creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadWebsiteFixtures()
    {
        require __DIR__ . '/../_files/websiteFixtures.php';
    }

    /**
     * Rolls back website creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadWebsiteFixturesRollback()
    {
        require __DIR__ . '/../_files/websiteFixtures_rollback.php';
    }
}
