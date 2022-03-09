<?php
/** @noinspection PhpSameParameterValueInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Klevu\Metadata\Test\Integration\Provider;

use Klevu\Metadata\Api\CheckoutMetadataProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CheckoutMetadataProviderTest extends TestCase
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
    public function testGetMetadataForCartSimpleProducts()
    {
        $this->setupPhp5();

        $quote = $this->createNewGuestCart(1, [
            'klevu_simple_1' => 1,
            'klevu_simple_3' => 2,
        ]);

        /** @var CheckoutMetadataProviderInterface $checkoutMetadataProvider */
        $checkoutMetadataProvider = $this->objectManager->get(CheckoutMetadataProviderInterface::class);
        $actualResult = $checkoutMetadataProvider->getMetadataForCart($quote);

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($actualResult);
        } else {
            $this->asserttrue(is_array($actualResult));
        }

        $expectedArrayKeys = [
            'platform',
            'pageType',
            'cartRecords',
        ];
        $this->assertSameSize($expectedArrayKeys, $actualResult);
        foreach ($expectedArrayKeys as $expectedArrayKey) {
            $this->assertArrayHasKey($expectedArrayKey, $actualResult);
        }
        $this->assertSame('magento2', $actualResult['platform']);
        $this->assertSame('cart', $actualResult['pageType']);

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($actualResult['cartRecords']);
        } else {
            $this->assertTrue(is_array($actualResult['cartRecords']));
        }
        /** @noinspection PhpParamsInspection */
        $this->assertCount(2, $actualResult['cartRecords']);

        $expectedCartRecordArrayKeys = [
            'itemId',
            'itemGroupId',
        ];
        foreach ($actualResult['cartRecords'] as $cartRecordResult) {
            if (method_exists($this, 'assertIsArray')) {
                $this->assertIsArray($cartRecordResult);
            } else {
                $this->assertTrue(is_array($cartRecordResult));
            }
            $this->assertSameSize($expectedCartRecordArrayKeys, $cartRecordResult);
            foreach ($expectedCartRecordArrayKeys as $expectedCartRecordArrayKey) {
                $this->assertArrayHasKey($expectedCartRecordArrayKey, $cartRecordResult);
            }
        }

        $product1 = $this->productRepository->get('klevu_simple_1');
        $this->assertSame([
            'itemId' => (string)$product1->getId(),
            'itemGroupId' => '',
        ], $actualResult['cartRecords'][0]);

        $product2 = $this->productRepository->get('klevu_simple_3');
        $this->assertSame([
            'itemId' => (string)$product2->getId(),
            'itemGroupId' => '',
        ], $actualResult['cartRecords'][1]);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     * @depends testGetMetadataForCartSimpleProducts
     */
    public function testGetMetadataForCartConfigurableProducts()
    {
        $this->setupPhp5();

        /** @var Product $configurableProduct */
        $configurableProduct = $this->productRepository->get('klevu_configurable_4');
        /** @var Product $simpleProduct */
        $simpleProduct = $this->productRepository->get('klevu_simple_child_1');
        /** @var Configurable $typeInstance */
        $typeInstance = $configurableProduct->getTypeInstance();
        $productAttributeOptions = $typeInstance->getConfigurableAttributesAsArray($configurableProduct);
        $superAttributeOptions = [];
        foreach ($productAttributeOptions as $productAttributeOption) {
            $superAttributeOptions[$productAttributeOption['attribute_id']]
                = $simpleProduct->getData($productAttributeOption['attribute_code']);
        }
        $requestParams = [
            'product' => $configurableProduct->getId(),
            'qty' => 1,
            'super_attribute' => $superAttributeOptions,
        ];

        $quote = $this->createNewGuestCart(1, [
            'klevu_configurable_4' => new DataObject($requestParams),
        ]);

        /** @var CheckoutMetadataProviderInterface $checkoutMetadataProvider */
        $checkoutMetadataProvider = $this->objectManager->get(CheckoutMetadataProviderInterface::class);
        $actualResult = $checkoutMetadataProvider->getMetadataForCart($quote);

        /** @noinspection PhpParamsInspection */
        $this->assertCount(1, $actualResult['cartRecords']);
        $this->assertSame([
            'itemId' => $configurableProduct->getId() . '-' . $simpleProduct->getId(),
            'itemGroupId' => (string)$configurableProduct->getId(),
        ], $actualResult['cartRecords'][0]);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     * @depends testGetMetadataForCartSimpleProducts
     */
    public function testGetMetadataForCartBundleProducts()
    {
        $this->setupPhp5();

        /** @var Product $bundleProduct */
        $bundleProduct = $this->productRepository->get('klevu_bundle_1');
        /** @var Product $simpleProduct */
        $simpleProduct = $this->productRepository->get('klevu_simple_child_1');

        $requestParams = [
            'product' => $bundleProduct->getId(),
            'item' => $bundleProduct->getId(),
            'bundle_option[' . $simpleProduct->getId() . ']' => 1
        ];

        $quote = $this->createNewGuestCart(1, [
            'klevu_bundle_1' => new DataObject($requestParams),
        ]);

        /** @var CheckoutMetadataProviderInterface $checkoutMetadataProvider */
        $checkoutMetadataProvider = $this->objectManager->get(CheckoutMetadataProviderInterface::class);
        $actualResult = $checkoutMetadataProvider->getMetadataForCart($quote);

        /** @noinspection PhpParamsInspection */
        $this->assertCount(1, $actualResult['cartRecords']);
        $this->assertSame([
            'itemId' => $bundleProduct->getId(),
            'itemGroupId' => '',
        ], $actualResult['cartRecords'][0]);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoCache all disabled
     * @magentoDataFixture loadProductFixtures
     * @depends testGetMetadataForCartSimpleProducts
     */
    public function testGetMetadataForCartGroupedProducts()
    {
        $this->setupPhp5();

        /** @var Product $groupedProduct */
        $groupedProduct = $this->productRepository->get('klevu_grouped_1');
        /** @var Product $simpleProduct */
        $simpleProduct = $this->productRepository->get('klevu_simple_child_1');

        $requestParams = [
            'product' => $groupedProduct->getId(),
            'item' => $groupedProduct->getId(),
            'super_group[' . $simpleProduct->getId() . ']' => 1
        ];
        $quote = $this->createNewGuestCart(1, [
            'klevu_grouped_1' => new DataObject($requestParams),
        ]);

        /** @var CheckoutMetadataProviderInterface $checkoutMetadataProvider */
        $checkoutMetadataProvider = $this->objectManager->get(CheckoutMetadataProviderInterface::class);
        $actualResult = $checkoutMetadataProvider->getMetadataForCart($quote);

        /** @noinspection PhpParamsInspection */
        $this->assertCount(1, $actualResult['cartRecords']);
        $this->assertSame([
            'itemId' => $groupedProduct->getId(),
            'itemGroupId' => '',
        ], $actualResult['cartRecords'][0]);

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
     * @param $storeId
     * @param array $skusRequests
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function createNewGuestCart($storeId, array $skusRequests)
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setStoreId($storeId);
        $quote->setIsActive(true);
        $quote->setIsMultiShipping(false);
        $quote->setCheckoutMethod('guest');
        $quote->setReservedOrderId('klevu_test_order_' . uniqid());
        $quote->setDataUsingMethod('email', 'no-reply@klevu.com');

        foreach ($skusRequests as $sku => $request) {
            $product = $this->productRepository->get($sku);
            $quote->addProduct($product, $request);
        }

        $quote->collectTotals();
        /** @noinspection PhpDeprecationInspection */
        $quote->save();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager->create(QuoteIdMaskFactory::class)->create();
        $quoteIdMask->setDataUsingMethod('quote_id', $quote->getId());
        $quoteIdMask->setDataChanges(true);
        /** @noinspection PhpDeprecationInspection */
        $quoteIdMask->save();

        return $quote;
    }
}