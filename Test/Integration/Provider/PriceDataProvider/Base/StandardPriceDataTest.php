<?php
/** @noinspection PhpUnhandledExceptionInspection */
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Klevu\Metadata\Test\Integration\Provider\PriceDataProvider\Base;

use Klevu\Metadata\Provider\ProductPriceDataProvider\Base as BaseProductPriceDataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForSimpleProduct_NoSpecialPrice()
    {
        $this->setupPhp5();

        /** @var BaseProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(BaseProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_simple_2');
        $expectedResults = [
            'price' => 20.2,
            'special_price' => null,
            'currency_code' => 'USD',
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
        $this->assertSame($expectedResults['currency_code'], $actualResult->getCurrencyCode());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetPriceDataForSimpleProduct_WithSpecialPrice()
    {
        $this->setupPhp5();

        /** @var BaseProductPriceDataProvider $productPriceDataProvider */
        $productPriceDataProvider = $this->objectManager->get(BaseProductPriceDataProvider::class);

        $product = $this->productRepository->get('klevu_simple_1');
        $expectedResults = [
            'price' => 10.0,
            'special_price' => 4.99,
            'currency_code' => 'USD',
        ];

        $actualResult = $productPriceDataProvider->getPriceDataForProduct($product);

        $this->assertSame($expectedResults['price'], $actualResult->getPrice());
        $this->assertSame($expectedResults['special_price'], $actualResult->getSpecialPrice());
        $this->assertSame($expectedResults['currency_code'], $actualResult->getCurrencyCode());
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
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
