<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpSameParameterValueInspection */
/** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */
/** @noinspection PhpUnhandledExceptionInspection */

namespace Klevu\Metadata\Test\Integration\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController as AbstractControllerTestCase;

class ProductPageOutputTest extends AbstractControllerTestCase
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $urlSuffix;

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture default_store klevu_search/metadata/enabled 1
     * @magentoDataFixture loadProductFixtures
     * @noinspection PhpParamsInspection
     */
    public function testJavascriptIsOutputToPageWhenEnabled()
    {
        $this->setupPhp5();

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $defaultStore = $storeManager->getStore('default');
        $storeManager->setCurrentStore($defaultStore->getId());

        $product = $this->productRepository->get('klevu_simple_1', false, $defaultStore->getId());
        $url = $this->prepareUrl($product->getUrlKey());

        $this->dispatch($url);

        $response = $this->getResponse();
        $responseBody = $response->getBody();
        $this->assertSame(200, $response->getHttpResponseCode());
        $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
        $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"pdp"#', $responseBody);
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 0
     * @magentoConfigFixture default_store klevu_search/metadata/enabled 0
     * @magentoDataFixture loadProductFixtures
     * @noinspection PhpParamsInspection
     */
    public function testJavascriptIsNotOutputToPageWhenDisabled()
    {
        $this->setupPhp5();

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $defaultStore = $storeManager->getStore('default');
        $storeManager->setCurrentStore($defaultStore->getId());

        $product = $this->productRepository->get('klevu_simple_1', false, $defaultStore->getId());
        $url = $this->prepareUrl($product->getUrlKey());

        $this->dispatch($url);

        $response = $this->getResponse();
        $responseBody = $response->getBody();
        $this->assertSame(200, $response->getHttpResponseCode());
        $this->assertStringNotContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        $this->assertDoesNotMatchRegularExpression('#klevu_page_meta\s*=#', $responseBody);
        $this->assertDoesNotMatchRegularExpression('#"pageType"\s*:\s*"pdp"#', $responseBody);
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->urlSuffix = $this->scopeConfig->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
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
     * Loads product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadProductFixtures()
    {
        include __DIR__ . '/../_files/productFixtures.php';
    }

    /**
     * Rolls back creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadCategoryFixturesRollback()
    {
        include __DIR__ . '/../_files/productFixtures_rollback.php';
    }
}
