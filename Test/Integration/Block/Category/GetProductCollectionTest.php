<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpSameParameterValueInspection */
/** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Klevu\Metadata\Test\Integration\Block\Category;

use Klevu\Metadata\Block\Category as CategoryBlock;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\AbstractController as AbstractControllerTestCase;

class GetProductCollectionTest extends AbstractControllerTestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $urlSuffix;

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture default_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoConfigFixture default_store klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoDataFixture loadCategoryFixtures
     * @magentoDbIsolation disabled
     * @noinspection PhpParamsInspection
     */
    public function testGetProductCollection_ValidBlock()
    {
        $this->setupPhp5();

        $this->dispatch($this->prepareUrl('klevu-test-category-1'));

        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);

        /** @var CategoryBlock $categoryBlock */
        $categoryBlock = $layout->getBlock('klevu_metadata_category');
        $this->assertInstanceOf(
            CategoryBlock::class,
            $categoryBlock,
            'klevu_metadata_category instance of Block\Category'
        );

        $listProductsBlockName = $categoryBlock->getData('list_products_block_name');
        $this->assertNotEmpty($listProductsBlockName, 'list_products_block_name is not empty');

        $productCollection = $categoryBlock->getProductCollection();
        $this->assertInstanceOf(
            ProductCollection::class,
            $productCollection,
            'productCollection instanceof Product\Collection'
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture default_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoConfigFixture default_store klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoDataFixture loadCategoryFixtures
     * @magentoDbIsolation disabled
     */
    public function testGetProductsCollection_InvalidBlock()
    {
        $this->setupPhp5();

        $this->dispatch($this->prepareUrl('klevu-test-category-1'));

        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);

        /** @var CategoryBlock $categoryBlock */
        $categoryBlock = $layout->getBlock('klevu_metadata_category');
        /** @noinspection PhpParamsInspection */
        $this->assertInstanceOf(
            CategoryBlock::class,
            $categoryBlock,
            'klevu_metadata_category instance of Block\Category'
        );

        $categoryBlock->setData('list_products_block_name', 'category.view.container');
        $listProductsBlockName = $categoryBlock->getData('list_products_block_name');
        $this->assertNotEmpty($listProductsBlockName, 'list_products_block_name is not empty');

        $productCollection = $categoryBlock->getProductCollection();
        $this->assertNull($productCollection, 'productCollection is NULL');
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture default_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoConfigFixture default_store klevu_search/categorylanding/enabledcategorynavigation 1
     * @magentoDataFixture loadCategoryFixtures
     * @magentoDbIsolation disabled
     */
    public function testGetProductsCollection_EmptyBlock()
    {
        $this->setupPhp5();

        $this->dispatch($this->prepareUrl('klevu-test-category-1'));

        /** @var LayoutInterface $layout */
        $layout = $this->objectManager->get(LayoutInterface::class);

        /** @var CategoryBlock $categoryBlock */
        $categoryBlock = $layout->getBlock('klevu_metadata_category');
        /** @noinspection PhpParamsInspection */
        $this->assertInstanceOf(
            CategoryBlock::class,
            $categoryBlock,
            'klevu_metadata_category instance of Block\Category'
        );

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $categoryBlock->setData('list_products_block_name', null);
        $listProductsBlockName = $categoryBlock->getData('list_products_block_name');
        $this->assertEmpty($listProductsBlockName, 'list_products_block_name is empty');

        $productCollection = $categoryBlock->getProductCollection();
        $this->assertNull($productCollection, 'productCollection is NULL');
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->urlSuffix = $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Prepare url to dispatch
     *
     * @param string $urlKey
     * @param bool $addSuffix
     * @return string
     */
    private function prepareUrl($urlKey, $addSuffix = true)
    {
        return $addSuffix ? '/' . $urlKey . $this->urlSuffix : '/' . $urlKey;
    }

    /**
     * Loads category creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadCategoryFixtures()
    {
        include __DIR__ . '/../../_files/categoryFixtures.php';
    }

    /**
     * Rolls back category creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadCategoryFixturesRollback()
    {
        include __DIR__ . '/../../_files/categoryFixtures_rollback.php';
    }
}
