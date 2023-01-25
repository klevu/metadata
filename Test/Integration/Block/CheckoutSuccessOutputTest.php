<?php

namespace Klevu\Metadata\Test\Integration\Block;

use Exception;
use Klevu\Metadata\Service\Convertor\OrderSyncItemDataConvertor;
use Klevu\Search\Api\Provider\Sync\Order\ItemsToSyncProviderInterface;
use Klevu\Search\Provider\Sync\Order\Item\Type\DefaultDataProvider;
use Klevu\Search\Provider\Sync\Order\ItemsToSyncProvider;
use Magento\Bundle\Model\Link as BundleLink;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\AbstractController as AbstractControllerTestCase;

class CheckoutSuccessOutputTest extends AbstractControllerTestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var QuoteManagement
     */
    private $quoteManagement;
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 0
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 0
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadSimpleProductFixtures
     */
    public function testMetaDataHiddenOnCheckoutSuccessWhenMetadataDisabled()
    {
        $this->setupPhp5();
        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Type::TYPE_SIMPLE,
                    'sku' => 'klevu_simple_1',
                ]
            ]
        );
        $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
        }
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertNotContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 0
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadSimpleProductFixtures
     */
    public function testMetaDataHiddenOnCheckoutSuccessWhenOrderSyncDisabled()
    {
        $this->setupPhp5();
        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Type::TYPE_SIMPLE,
                    'sku' => 'klevu_simple_1',
                ]
            ]
        );
        $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
        }
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertNotContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadSimpleProductFixtures
     */
    public function testHandlesExceptionsThrownByMissingData()
    {
        $this->setupPhp5();

        $exception = new \InvalidArgumentException(
            __('Required Order Item field %1 missing from analytics data', DefaultDataProvider::ORDER_ID)
        );
        $mockOrderItemConvertor = $this->getMockBuilder(OrderSyncItemDataConvertor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockOrderItemConvertor->expects($this->once())
            ->method('convert')
            ->willThrowException($exception);

        $this->objectManager->addSharedInstance($mockOrderItemConvertor, OrderSyncItemDataConvertor::class);

        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Type::TYPE_SIMPLE,
                    'sku' => 'klevu_simple_1',
                ]
            ]
        );
        $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
        }
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertNotContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadSimpleProductFixtures
     */
    public function testMetaDataShowsOnCheckoutSuccessForSimpleProduct()
    {
        $this->setupPhp5();
        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $product = $this->getProduct('klevu_simple_1');
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Type::TYPE_SIMPLE,
                    'sku' => $product->getSku(),
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*.*\s*Simple Product 1#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_group_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertMatchesRegularExpression(
                '#"item_variant_id"\s*:\s*"' . $product->getId() . '"#',
                $responseBody
            );
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*15#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*.*\s*Simple Product 1#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*15#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadConfigurableProductFixtures
     */
    public function testMetaDataShowsOnCheckoutSuccessForConfigurableProduct()
    {
        $this->setupPhp5();
        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $childProduct = $this->getProduct('klevu_simple_child_1');
        $parentProduct = $this->getProduct('klevu_configurable_1');
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Configurable::TYPE_CODE,
                    'sku' => $parentProduct->getSku(),
                    'child' => $childProduct->getSku(),
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        $expectedItemId = $parentProduct->getId() . '-' . $childProduct->getId();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*.*\s*Simple Child Product 1#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression(
                '#"item_group_id"\s*:\s*"' . $parentProduct->getId() . '"#',
                $responseBody
            );
            $this->assertMatchesRegularExpression(
                '#"item_variant_id"\s*:\s*"' . $childProduct->getId() . '"#',
                $responseBody
            );
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*30#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*.*\s*Simple Child Product 1#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $parentProduct->getId() . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $childProduct->getId() . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*30#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadGroupedProductFixtures
     */
    public function testMetaDataShowsOnCheckoutSuccessForGroupedProduct()
    {
        $this->setupPhp5();
        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $childProduct1 = $this->getProduct('klevu_simple_grouped_child_1');
        $childProduct2 = $this->getProduct('klevu_simple_grouped_child_2');
        $parentProduct = $this->getProduct('klevu_grouped_1');
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Grouped::TYPE_CODE,
                    'sku' => $parentProduct->getSku(),
                    'child' => [
                        $childProduct1->getSku() => 1,
                        $childProduct2->getSku() => 2
                    ],
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        $expectedItemId = $parentProduct->getId();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*.*\s*Simple Child Product#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*150#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*.*\s*Simple Child Product#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*150#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadBundleProductFixtures
     */
    public function testMetaDataShowsOnCheckoutSuccessForBundleProduct()
    {
        $this->setupPhp5();
        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $childProduct1 = $this->getProduct('klevu_simple_bundle_child_1');
        $childProduct2 = $this->getProduct('klevu_simple_bundle_child_2');
        $parentProduct = $this->getProduct('klevu_bundle_1');
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => BundleType::TYPE_CODE,
                    'sku' => $parentProduct->getSku(),
                    'child' => [
                        $childProduct1->getSku() => 1,
                        $childProduct2->getSku() => 2
                    ]
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        $expectedItemId = $parentProduct->getId();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*.*\s*Bundle Product 1#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*112.5#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*.*\s*Bundle Product 1#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*112.5#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadSimpleProductFixtures
     */
    public function testUsesCheckoutDataIfNoDatabaseEntryForOrderSyncForSimpleProduct()
    {
        $this->setupPhp5();

        $mockItemsToSyncProvider = $this->getMockBuilder(ItemsToSyncProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockItemsToSyncProvider->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->objectManager->addSharedInstance($mockItemsToSyncProvider, ItemsToSyncProvider::class);

        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $product = $this->getProduct('klevu_simple_1');
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Type::TYPE_SIMPLE,
                    'sku' => $product->getSku(),
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
        }
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*.*\s*Simple Product 1#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_group_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertMatchesRegularExpression(
                '#"item_variant_id"\s*:\s*"' . $product->getId() . '"#',
                $responseBody
            );
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*15#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*.*\s*Simple Product 1#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $product->getId() . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*15#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }
    }


    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadConfigurableProductFixtures
     */
    public function testUsesCheckoutDataIfNoDatabaseEntryForOrderSyncForConfigurableProduct()
    {
        $this->setupPhp5();

        $mockItemsToSyncProvider = $this->getMockBuilder(ItemsToSyncProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockItemsToSyncProvider->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->objectManager->addSharedInstance($mockItemsToSyncProvider, ItemsToSyncProvider::class);

        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $childProduct = $this->getProduct('klevu_simple_child_1');
        $parentProduct = $this->getProduct('klevu_configurable_1');
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Configurable::TYPE_CODE,
                    'sku' => $parentProduct->getSku(),
                    'child' => $childProduct->getSku(),
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        $expectedItemId = $parentProduct->getId() . '-' . $childProduct->getId();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*.*\s*Simple Child Product 1#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression(
                '#"item_group_id"\s*:\s*"' . $parentProduct->getId() . '"#',
                $responseBody
            );
            $this->assertMatchesRegularExpression(
                '#"item_variant_id"\s*:\s*"' . $childProduct->getId() . '"#',
                $responseBody
            );
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*30#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*.*\s*Simple Child Product 1#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $parentProduct->getId() . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $childProduct->getId() . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*30#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }
    }


    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadGroupedProductFixtures
     */
    public function testUsesCheckoutDataIfNoDatabaseEntryForOrderSyncForGroupedProduct()
    {
        $this->setupPhp5();

        $mockItemsToSyncProvider = $this->getMockBuilder(ItemsToSyncProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockItemsToSyncProvider->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->objectManager->addSharedInstance($mockItemsToSyncProvider, ItemsToSyncProvider::class);

        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $childProduct1 = $this->getProduct('klevu_simple_grouped_child_1');
        $childProduct2 = $this->getProduct('klevu_simple_grouped_child_2');
        $parentProduct = $this->getProduct('klevu_grouped_1');
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => Grouped::TYPE_CODE,
                    'sku' => $parentProduct->getSku(),
                    'child' => [
                        $childProduct1->getSku() => 1,
                        $childProduct2->getSku() => 2
                    ],
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        $expectedItemId = $parentProduct->getId();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*"\[Klevu\] Simple Child Product \d"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*150#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*"\[Klevu\] Simple Child Product \d"#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*150#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }

        // ensure only one item is returned for grouped products not all children
        $matches = [];
        preg_match_all(
            '#"item_name"\s*:\s*"\[Klevu\] Simple Child Product \d"#',
            $responseBody,
            $matches
        );
        $this->assertCount(1, $matches[0]);
    }

    /**
     * @magentoAppArea frontend
     * @magentoCache all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/klevu_search/metadata/enabled 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/enabled 1
     * @magentoConfigFixture default/klevu_search/metadata/ordersync 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/metadata/ordersync 1
     * @magentoConfigFixture default/klevu_search/developer/theme_version v1
     * @magentoConfigFixture klevu_test_store_1_store klevu_search/developer/theme_version v1
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture default_store currency/options/default EUR
     * @magentoConfigFixture klevu_test_store_1_store currency/options/allow USD,JPY,EUR,GBP
     * @magentoConfigFixture klevu_test_store_1_store currency/options/default GBP
     * @magentoDataFixture loadBundleProductFixtures
     */
    public function testUsesCheckoutDataIfNoDatabaseEntryForOrderSyncForBundleProduct()
    {
        $this->setupPhp5();

        $mockItemsToSyncProvider = $this->getMockBuilder(ItemsToSyncProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockItemsToSyncProvider->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->objectManager->addSharedInstance($mockItemsToSyncProvider, ItemsToSyncProvider::class);

        $store = $this->getStore('klevu_test_store_1');
        $this->storeManager->setCurrentStore($store);
        $childProduct1 = $this->getProduct('klevu_simple_bundle_child_1');
        $childProduct2 = $this->getProduct('klevu_simple_bundle_child_2');
        $parentProduct = $this->getProduct('klevu_bundle_1');
        $customer = $this->getCustomer('customer@klevu.com', $store);
        $quote = $this->getCartForCustomer($customer);
        $this->addProductsToCart(
            $quote,
            [
                [
                    'type' => BundleType::TYPE_CODE,
                    'sku' => $parentProduct->getSku(),
                    'child' => [
                        $childProduct1->getSku() => 1,
                        $childProduct2->getSku() => 2
                    ]
                ]
            ]
        );
        $orderId = $this->placeOrder($quote);

        $this->dispatch('/checkout/onepage/success');
        $response = $this->getResponse();
        $responseBody = $response->getBody();

        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Success Page', $responseBody);
            $this->assertStringContainsString('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        } else {
            $this->assertContains('Success Page', $responseBody);
            $this->assertContains('<script type="text/javascript" id="klevu_page_meta">', $responseBody);
        }

        $expectedItemId = $parentProduct->getId();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#klevu_page_meta\s*=#', $responseBody);
            $this->assertMatchesRegularExpression('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertMatchesRegularExpression('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertMatchesRegularExpression('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_name"\s*:\s*.*\s*Bundle Product 1#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertMatchesRegularExpression('#"unit_price"\s*:\s*112.5#', $responseBody);
            $this->assertMatchesRegularExpression('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertMatchesRegularExpression('#"units"\s*:\s*1#', $responseBody);
        } else {
            $this->assertRegExp('#klevu_page_meta\s*=#', $responseBody);
            $this->assertRegExp('#"platform"\s*:\s*"magento2"#', $responseBody);
            $this->assertRegExp('#"pageType"\s*:\s*"checkout"#', $responseBody);
            $this->assertRegExp('#"orderItems"\s*:\s*\[\s*\{#', $responseBody);
            $this->assertRegExp('#"order_id"\s*:\s*"' . $orderId . '"#', $responseBody);
            $this->assertRegExp('#"order_line_id"\s*:\s*"[0-9]*"#', $responseBody);
            $this->assertRegExp('#"item_name"\s*:\s*.*\s*Bundle Product 1#', $responseBody);
            $this->assertRegExp('#"item_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_group_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"item_variant_id"\s*:\s*"' . $expectedItemId . '"#', $responseBody);
            $this->assertRegExp('#"unit_price"\s*:\s*112.5#', $responseBody);
            $this->assertRegExp('#"currency"\s*:\s*"GBP"#', $responseBody);
            $this->assertRegExp('#"units"\s*:\s*1#', $responseBody);
        }
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function getCartForCustomer(CustomerInterface $customer)
    {
        return $this->quoteManagement->getCartForCustomer($customer->getId());
    }

    /**
     * @param CartInterface $quote
     * @param array $productsToAdd
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    private function addProductsToCart(CartInterface $quote, array $productsToAdd)
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $productRepository->cleanCache();

        foreach ($productsToAdd as $productToAdd) {
            $product = $productRepository->get($productToAdd['sku']);
            $params = [];
            $params['product'] = $product->getId();
            $params['qty'] = isset($productToAdd['qty']) ? $productToAdd['qty'] : 1;
            $options = [];
            if ($productToAdd['type'] === Configurable::TYPE_CODE) {
                $childProduct = $productRepository->get($productToAdd['child']);

                $typeInstance = $product->getTypeInstance();
                $productAttributeOptions = $typeInstance->getConfigurableAttributesAsArray($product);

                foreach ($productAttributeOptions as $option) {
                    $options[$option['attribute_id']] = $childProduct->getData($option['attribute_code']);
                }
                $params['super_attribute'] = $options;
            }
            if ($productToAdd['type'] === Grouped::TYPE_CODE) {
                $params['super_group'] = $productToAdd['child']; // ['sku1' => 'qty1', 'sku2' => 'qty2]
            }
            if ($productToAdd['type'] === BundleType::TYPE_CODE) {
                $skus = array_keys($productToAdd['child']);
                $bundleOption = [];
                $bundleOptionQty = [];
                $extensionAttributes = $product->getExtensionAttributes();
                $bundleProductOptions = $extensionAttributes->getBundleProductOptions();
                foreach ($bundleProductOptions as $option) {
                    $links = array_filter($option->getProductLinks(), static function (BundleLink $link) use ($skus) {
                        return in_array($link->getSku(), $skus, true);
                    });

                    $bundleLinks = array_map(static function (BundleLink $link) {
                        return $link->getId();
                    }, $links);

                    $bundleQuantities = array_map(
                        static function (BundleLink $link) use ($productToAdd) {
                            return isset($productToAdd['child'][$link->getSku()])
                                ? $productToAdd['child'][$link->getSku()]
                                : $link->getQty();
                        },
                        $links
                    );

                    $linkKeys = array_keys($bundleLinks);
                    $bundleOption[$option->getId()] = isset($bundleLinks[$linkKeys[0]]) ? $bundleLinks[$linkKeys[0]] : null;
                    $qtyKeys = array_keys($bundleQuantities);
                    $bundleOptionQty[$option->getId()] = isset($bundleQuantities[$qtyKeys[0]]) ? $bundleQuantities[$qtyKeys[0]] : 1;
                }

                $params['bundle_option'] = $bundleOption;
                $params['bundle_option_qty'] = $bundleOptionQty;
            }
            $quote->addProduct($product, new DataObject($params));
        }
        $quote->collectTotals();
        /** @noinspection PhpDeprecationInspection */
        $quote->save();
    }

    /**
     * @param CartInterface $quote
     *
     * @return int Order ID.
     * @throws CouldNotSaveException
     */
    private function placeOrder(CartInterface $quote)
    {
        return $this->quoteManagement->placeOrder($quote->getId());
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManager::class);
        $this->quoteManagement = $this->objectManager->get(QuoteManagement::class);
    }

    /**
     * @param string $sku
     *
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProduct($sku)
    {
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        return $productRepository->get($sku);
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
     * @param string $email
     * @param StoreInterface $store
     *
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomer($email, $store)
    {
        $websiteId = $store->getWebsiteId();
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);

        return $customerRepository->get($email, $websiteId);
    }

    /**
     * Loads bundle product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadBundleProductFixtures()
    {
        require __DIR__ . '/../_files/websiteFixtures.php';
        require __DIR__ . '/../_files/product_bundleFixtures.php';
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures.php';
    }

    /**
     * Rolls back bundle product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadBundleProductFixturesRollback()
    {
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures_rollback.php';
        require __DIR__ . '/../_files/product_bundleFixtures_rollback.php';
        require __DIR__ . '/../_files/websiteFixtures_rollback.php';
    }

    /**
     * Loads grouped product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadGroupedProductFixtures()
    {
        require __DIR__ . '/../_files/websiteFixtures.php';
        require __DIR__ . '/../_files/product_groupedFixtures.php';
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures.php';
    }

    /**
     * Rolls back grouped product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadGroupedProductFixturesRollback()
    {
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures_rollback.php';
        require __DIR__ . '/../_files/product_groupedFixtures_rollback.php';
        require __DIR__ . '/../_files/websiteFixtures_rollback.php';
    }

    /**
     * Loads configurable product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadConfigurableProductFixtures()
    {
        require __DIR__ . '/../_files/websiteFixtures.php';
        require __DIR__ . '/../_files/product_configurableFixtures.php';
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures.php';
    }

    /**
     * Rolls back configurable product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadConfigurableProductFixturesRollback()
    {
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures_rollback.php';
        require __DIR__ . '/../_files/product_configurableFixtures_rollback.php';
        require __DIR__ . '/../_files/websiteFixtures_rollback.php';
    }

    /**
     * Loads simple product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadSimpleProductFixtures()
    {
        require __DIR__ . '/../_files/websiteFixtures.php';
        require __DIR__ . '/../_files/product_simpleFixtures.php';
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures.php';
    }

    /**
     * Rolls back simple product creation scripts because annotations use a relative path
     *  from integration tests root
     */
    public static function loadSimpleProductFixturesRollback()
    {
        require __DIR__ . '/../_files/customerQuoteReadyForOrderFixtures_rollback.php';
        require __DIR__ . '/../_files/product_simpleFixtures_rollback.php';
        require __DIR__ . '/../_files/websiteFixtures_rollback.php';
    }
}
