<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */
/** @noinspection PhpSameParameterValueInspection */
/** @noinspection PhpUnhandledExceptionInspection */
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Klevu\Metadata\Test\Integration\Provider;

use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class ProductMetadataProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $urlSuffix;

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
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetMetadataForSimpleProduct()
    {
        $this->setupPhp5();

        $simpleProduct = $this->productRepository->get('klevu_simple_1');

        /** @var ProductMetadataProviderInterface $productMetadataProvider */
        $productMetadataProvider = $this->objectManager->create(ProductMetadataProviderInterface::class);
        $actualResult = $productMetadataProvider->getMetadataForProduct($simpleProduct);

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($actualResult);
        } else {
            $this->asserttrue(is_array($actualResult));
        }

        $expectedArrayKeys = [
            'platform',
            'pageType',
            'itemName',
            'itemUrl',
            'itemId',
            'itemGroupId',
            //'itemSalePrice',
            //'itemCurrency',
        ];
        $this->assertSameSize($expectedArrayKeys, $actualResult);
        foreach ($expectedArrayKeys as $expectedArrayKey) {
            $this->assertArrayHasKey($expectedArrayKey, $actualResult);
        }

        $this->assertSame('magento2', $actualResult['platform']);
        $this->assertSame('pdp', $actualResult['pageType'], 'pageType');
        $this->assertSame('[Klevu] Simple Product 1', $actualResult['itemName'], 'itemName');
        $this->assertSame($this->prepareUrl('klevu-simple-product-1'), $actualResult['itemUrl'], 'itemUrl');
        $this->assertSame((string)$simpleProduct->getId(), $actualResult['itemId'], 'itemId');
        $this->assertSame('', $actualResult['itemGroupId'], 'itemGroupId');
        //$this->assertSame('10.00', $actualResult['itemSalePrice'], 'itemSalePrice');
        //$this->assertSame('USD', $actualResult['itemCurrency'], 'itemCurrency');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetMetadataForConfigurableProduct_NoChildren()
    {
        $this->setupPhp5();

        $configurableProduct = $this->productRepository->get('klevu_configurable_1');

        /** @var ProductMetadataProviderInterface $productMetadataProvider */
        $productMetadataProvider = $this->objectManager->create(ProductMetadataProviderInterface::class);
        $actualResult = $productMetadataProvider->getMetadataForProduct($configurableProduct);

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($actualResult);
        } else {
            $this->asserttrue(is_array($actualResult));
        }

        $expectedArrayKeys = [
            'platform',
            'pageType',
            'itemName',
            'itemUrl',
            'itemId',
            'itemGroupId',
            //'itemSalePrice',
            //'itemCurrency',
        ];
        $this->assertSameSize($expectedArrayKeys, $actualResult);
        foreach ($expectedArrayKeys as $expectedArrayKey) {
            $this->assertArrayHasKey($expectedArrayKey, $actualResult);
        }

        $this->assertSame('magento2', $actualResult['platform']);
        $this->assertSame('pdp', $actualResult['pageType'], 'pageType');
        $this->assertSame('[Klevu] Configurable Product 1', $actualResult['itemName'], 'itemName');
        $this->assertSame($this->prepareUrl('klevu-configurable-product-1'), $actualResult['itemUrl'], 'itemUrl');
        $this->assertSame('', $actualResult['itemId'], 'itemId');
        $this->assertSame((string)$configurableProduct->getId(), $actualResult['itemGroupId'], 'itemGroupId');
        //$this->assertSame('', $actualResult['itemSalePrice'], 'itemSalePrice');
        //$this->assertSame('USD', $actualResult['itemCurrency'], 'itemCurrency');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     */
    public function testGetMetadataForConfigurableProduct_WithChildren()
    {
        $this->setupPhp5();

        $expectedSimpleProduct = $this->productRepository->get('klevu_simple_child_1');
        $configurableProduct = $this->productRepository->get('klevu_configurable_7');

        /** @var ProductMetadataProviderInterface $productMetadataProvider */
        $productMetadataProvider = $this->objectManager->create(ProductMetadataProviderInterface::class);
        $actualResult = $productMetadataProvider->getMetadataForProduct($configurableProduct);

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($actualResult);
        } else {
            $this->asserttrue(is_array($actualResult));
        }

        $expectedArrayKeys = [
            'platform',
            'pageType',
            'itemName',
            'itemUrl',
            'itemId',
            'itemGroupId',
            //'itemSalePrice',
            //'itemCurrency',
        ];
        $this->assertSameSize($expectedArrayKeys, $actualResult);
        foreach ($expectedArrayKeys as $expectedArrayKey) {
            $this->assertArrayHasKey($expectedArrayKey, $actualResult);
        }

        $this->assertSame('magento2', $actualResult['platform'], 'platform');
        $this->assertSame('pdp', $actualResult['pageType'], 'pageType');
        $this->assertSame('[Klevu] Configurable Product 7', $actualResult['itemName'], 'itemName');
        $this->assertSame($this->prepareUrl('klevu-configurable-product-7'), $actualResult['itemUrl'], 'itemUrl');
        /// @todo Resolve issues in KS-6044 and reinstate tests for 2.1.x
        if (version_compare($this->magentoVersion, '2.2.0', '>=')) {
            $this->assertSame(
                (string)$configurableProduct->getId() . '-' . (string)$expectedSimpleProduct->getId(),
                $actualResult['itemId'],
                'itemId'
            );
            $this->assertSame((string)$configurableProduct->getId(), $actualResult['itemGroupId'], 'itemGroupId');
        }
        //$this->assertSame('99.99', $actualResult['itemSalePrice'], 'itemSalePrice');
        //$this->assertSame('USD', $actualResult['itemCurrency'], 'itemCurrency');
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     * @depends testGetMetadataForConfigurableProduct_WithChildren
     */
    public function testGetMetadataForConfigurableProduct_FirstChildDisabled()
    {
        $this->setupPhp5();

        $originalSimpleProduct = $this->productRepository->get('klevu_simple_child_1');
        $originalSimpleProduct->setStatus(Status::STATUS_DISABLED);
        $this->productRepository->save($originalSimpleProduct);
        $this->productRepository->cleanCache();

        $expectedSimpleProduct = $this->productRepository->get('klevu_simple_child_4');
        $configurableProduct = $this->productRepository->get('klevu_configurable_7');

        /** @var ProductMetadataProviderInterface $productMetadataProvider */
        $productMetadataProvider = $this->objectManager->create(ProductMetadataProviderInterface::class);
        $actualResult = $productMetadataProvider->getMetadataForProduct($configurableProduct);

        /// @todo Resolve issues in KS-6044 and reinstate tests for 2.1.x
        if (version_compare($this->magentoVersion, '2.2.0', '>=')) {
            $this->assertSame(
                (string)$configurableProduct->getId() . '-' . (string)$expectedSimpleProduct->getId(),
                $actualResult['itemId'],
                'itemId'
            );
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     * @depends testGetMetadataForConfigurableProduct_WithChildren
     */
    public function testGetMetadataForConfigurableProduct_FirstChildOOS()
    {
        $this->markTestSkipped();
        $this->setupPhp5();

        $originalSimpleProduct = $this->productRepository->get('klevu_simple_child_1');
        $originalSimpleProduct->setStockData(['qty' => 0, 'is_in_stock' => false]);
        $originalSimpleProduct->setQuantityAndStockStatus(['qty' => 0, 'is_in_stock' => false]);
        $this->productRepository->save($originalSimpleProduct);
        $this->productRepository->cleanCache();

        $indexerFactory = $this->objectManager->get(IndexerFactory::class);
        $indexes = [
            'catalog_product_attribute',
            'catalog_product_price',
            'inventory',
            'cataloginventory_stock',
        ];
        foreach ($indexes as $index) {
            $indexer = $indexerFactory->create();
            try {
                $indexer->load($index);
                $indexer->reindexAll();
            } catch (\InvalidArgumentException $e) {
                // Support for older versions of Magento which may not have all indexers
                continue;
            }
        }

        $expectedSimpleProduct = $this->productRepository->get('klevu_simple_child_4');
        $configurableProduct = $this->productRepository->get('klevu_configurable_7');

        /** @var ProductMetadataProviderInterface $productMetadataProvider */
        $productMetadataProvider = $this->objectManager->create(ProductMetadataProviderInterface::class);
        $actualResult = $productMetadataProvider->getMetadataForProduct($configurableProduct);

        /// @todo Resolve issues in KS-6044 and reinstate tests for 2.1.x
        if (version_compare($this->magentoVersion, '2.2.0', '>=')) {
            $this->assertSame(
                (string)$configurableProduct->getId() . '-' . (string)$expectedSimpleProduct->getId(),
                $actualResult['itemId'],
                'itemId'
            );
        }
    }

    /**
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     * @todo Implement testGetMetadataForBundleProduct
     */
//    public function testGetMetadataForBundleProduct()
//    {
//        $this->markTestSkipped('Not implemented');
//    }

    /**
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     * @todo Implement testGetMetadataForGroupedProduct
     */
//    public function testGetMetadataForGroupedProduct()
//    {
//        $this->markTestSkipped('Not implemented');
//    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->urlSuffix = $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->magentoVersion = $this->objectManager->get(ProductMetadataInterface::class)->getVersion();
    }

    /**
     * Loads product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadProductFixtures()
    {
        require __DIR__ . '/../_files/productFixtures.php';
    }

    /**
     * Rolls back product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadProductFixturesRollback()
    {
        require __DIR__ . '/../_files/productFixtures_rollback.php';
    }

    /**
     * @param $urlKey
     * @param bool $addBaseUrl
     * @param bool $addSuffix
     * @return string
     * @throws NoSuchEntityException
     */
    private function prepareUrl($urlKey, $addBaseUrl = true, $addSuffix = true)
    {
        $return = '';

        if ($addBaseUrl) {
            $store = $this->storeManager->getStore();
            $return .= $store->getBaseUrl();
        }

        $return .= $urlKey;

        if ($addSuffix) {
            $return .= $this->urlSuffix;
        }

        return $return;
    }
}
