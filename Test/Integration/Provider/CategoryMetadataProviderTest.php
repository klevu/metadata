<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpSameParameterValueInspection */
/** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */
/** @noinspection PhpUnhandledExceptionInspection */

namespace Klevu\Metadata\Test\Integration\Provider;

use Klevu\Metadata\Api\CategoryMetadataProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CategoryMetadataProviderTest extends TestCase
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $urlSuffix;

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoConfigFixture default_store klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoDataFixture loadCategoryFixtures
     * @magentoDataFixture loadProductFixtures
     * @magentoDataFixture loadCategoryProductAssociationFixtures
     */
    public function testGetMetadataForCategoryWithoutProductCollection()
    {
        $this->setupPhp5();

        /** @var  $collection */
        $collection = $this->objectManager->create(CategoryCollection::class);
        $collection->addAttributeToFilter('url_key', 'klevu-test-category-1-1');
        $collection->addAttributeToSelect('*');
        $collection->load();

        /** @var Category $category */
        $category = $collection->getFirstItem();

        /** @var CategoryMetadataProviderInterface $categoryMetadataProvider */
        $categoryMetadataProvider = $this->objectManager->get(CategoryMetadataProviderInterface::class);
        $actualResult = $categoryMetadataProvider->getMetadataForCategory($category);

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($actualResult);
        } else {
            $this->asserttrue(is_array($actualResult));
        }

        $expectedArrayKeys = [
            'platform',
            'pageType',
            'categoryName',
            'categoryUrl',
            'categoryProducts',
        ];
        $this->assertSameSize($expectedArrayKeys, $actualResult);
        foreach ($expectedArrayKeys as $expectedArrayKey) {
            $this->assertArrayHasKey($expectedArrayKey, $actualResult);
        }
        $this->assertSame('magento2', $actualResult['platform']);
        $this->assertSame('category', $actualResult['pageType']);
        $this->assertSame(
            '[Klevu] Parent Category 1;[Klevu] Child Category 1-1',
            $actualResult['categoryName']
        );
        $this->assertSame(
            $this->prepareUrl('klevu-test-category-1/klevu-test-category-1-1'),
            $actualResult['categoryUrl']
        );

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($actualResult['categoryProducts']);
        } else {
            $this->assertTrue(is_array($actualResult['categoryProducts']));
        }
        $expectedProductArrayKeys = [
            'itemId',
            'itemGroupId',
        ];

        foreach ($actualResult['categoryProducts'] as $productResult) {
            if (method_exists($this, 'assertIsArray')) {
                $this->assertIsArray($productResult);
            } else {
                $this->assertTrue(is_array($productResult));
            }
            $this->assertSameSize($expectedProductArrayKeys, $productResult);
            foreach ($expectedProductArrayKeys as $expectedProductArrayKey) {
                $this->assertArrayHasKey($expectedProductArrayKey, $productResult);
            }
        }

        $expectedSkus = [
            'klevu_simple_1',
            'klevu_simple_3',
            'klevu_simple_4',
            'klevu_configurable_4',
        ];

        $this->assertSameSize($expectedSkus, $actualResult['categoryProducts']);
        foreach ($expectedSkus as $sku) {
            $product = $this->productRepository->get($sku);
            switch ($product->getTypeId()) {
                case 'simple':
                    $expectedCategoryProductItem = [
                        'itemId' => (string)$product->getId(),
                        'itemGroupId' => '',
                    ];
                    break;

                case 'configurable':
                    $expectedCategoryProductItem = [
                        'itemId' => '',
                        'itemGroupId' => (string)$product->getId(),
                    ];
                    break;

                default:
                    $expectedCategoryProductItem = null;
                    break;
            }

            $this->assertContains($expectedCategoryProductItem, $actualResult['categoryProducts']);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadCategoryFixtures
     * @magentoDataFixture loadProductFixtures
     * @magentoDataFixture loadCategoryProductAssociationFixtures
     * @depends testGetMetadataForCategoryWithoutProductCollection
     */
    public function testGetMetadataForCategoryWithLoadedProductCollection()
    {
        $this->setupPhp5();

        /** @var  $collection */
        $collection = $this->objectManager->create(CategoryCollection::class);
        $collection->addAttributeToFilter('url_key', 'klevu-test-category-1-1');
        $collection->addAttributeToSelect('*');
        $collection->load();

        /** @var Category $category */
        $category = $collection->getFirstItem();

        /** @var ProductCollection $productCollectionOverride */
        $productCollectionOverride = $this->objectManager->create(ProductCollection::class);
        $productCollectionOverride->setFlag('has_stock_status_filter', true);
        $productCollectionOverride->addAttributeToFilter('sku', ['in' => [
            'klevu_simple_1',
            'klevu_simple_2',
            'klevu_simple_3',
            'klevu_simple_4',
            'klevu_simple_5',
        ]]);
        $productCollectionOverride->setPage(2, 3);

        /** @var CategoryMetadataProviderInterface $categoryMetadataProvider */
        $categoryMetadataProvider = $this->objectManager->get(CategoryMetadataProviderInterface::class);
        $actualResult = $categoryMetadataProvider->getMetadataForCategory(
            $category,
            $productCollectionOverride
        );

        $expectedSkus = [
            'klevu_simple_4',
            'klevu_simple_5',
        ];

        $this->assertSameSize($expectedSkus, $actualResult['categoryProducts']);
        foreach ($expectedSkus as $sku) {
            $product = $this->productRepository->get($sku);
            switch ($product->getTypeId()) {
                case 'simple':
                    $expectedCategoryProductItem = [
                        'itemId' => (string)$product->getId(),
                        'itemGroupId' => '',
                    ];
                    break;

                case 'configurable':
                    $expectedCategoryProductItem = [
                        'itemId' => '',
                        'itemGroupId' => (string)$product->getId(),
                    ];
                    break;
            }

            $this->assertContains($expectedCategoryProductItem, $actualResult['categoryProducts']);
        }
    }

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
    }

    /**
     * Loads category creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadCategoryFixtures()
    {
        require __DIR__ . '/../_files/categoryFixtures.php';
    }

    /**
     * Rolls back category creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadCategoryFixturesRollback()
    {
        require __DIR__ . '/../_files/categoryFixtures_rollback.php';
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
     * Loads product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadCategoryProductAssociationFixtures()
    {
        require __DIR__ . '/../_files/categoryProductAssociationFixtures.php';
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
