<?php
/** @noinspection PhpUnhandledExceptionInspection */
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Klevu\Metadata\Test\Integration\Provider\PriceDataProvider\Configurable;

use Klevu\Metadata\Provider\ProductPriceDataProvider\Configurable as ConfigurableProductPriceDataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class StandardPriceDataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $magentoVersion;

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_NoChildren()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_1');
        $expectedResults = [
            'price' => null,
            'special_price' => null,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_NoAvailableChildren()
    {
        $this->markTestSkipped();

        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_2');
        $expectedResults = [
            'price' => null,
            'special_price' => null,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_SingleChild_NoSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_3');
        $expectedResults = [
            'price' => 9.99,
            'special_price' => null,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_SingleChild_WithSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_4');
        $expectedResults = [
            'price' => 99.99,
            'special_price' => 49.99,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_MultipleChildren_1OOSLowerPriceLowerSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_5');
        $expectedResults = [
            'price' => 99.99,
            'special_price' => 49.99,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_MultipleChildren_1DisabledLowerPriceLowerSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_6');
        $expectedResults = [
            'price' => 99.99,
            'special_price' => 49.99,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_MultipleChildren_1SamePriceLowerSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_7');
        $expectedResults = [
            'price' => 99.99,
            'special_price' => 49.99,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_MultipleChildren_1LowerPriceLowerSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_8');
        $expectedResults = [
            'price' => 89.99,
            'special_price' => 39.99,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_MultipleChildren_1HigherPriceLowerSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_9');
        $expectedResults = [
            'price' => version_compare($this->magentoVersion, '2.2.0', '<')
                ? 99.99 // Magento changed its internal price data calculation for configurables
                : 109.99,
            'special_price' => 39.99,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_MultipleChildren_1MidPriceNoSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_10');
        $expectedResults = [
            'price' => version_compare($this->magentoVersion, '2.2.0', '<')
                ? 79.99 // Magento changed its internal price data calculation for configurables
                : 99.99,
            'special_price' => 49.99,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForProduct_MultipleChildren_1LowerPriceNoSpecialPrice()
    {
        $this->setupPhp5();

        /** @var ConfigurableProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(ConfigurableProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_configurable_11');
        $expectedResults = [
            'price' => 9.99,
            'special_price' => null,
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->magentoVersion = $this->objectManager->get(ProductMetadataInterface::class)->getVersion();
    }

    /**
     * Loads product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadProductFixtures()
    {
        require __DIR__ . '/../../../_files/productFixtures.php';
    }

    /**
     * Rolls back product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadProductFixturesRollback()
    {
        require __DIR__ . '/../../../_files/productFixtures_rollback.php';
    }
}
