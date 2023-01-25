<?php

use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Store\StoreManager;

include "customerQuoteReadyForOrderFixtures_rollback.php";

include "currencyRateFixtures.php";
include "customerFixtures.php";
include "customerAddressFixtures.php";

$objectManager = Bootstrap::getObjectManager();

/** @var Store $store */
$store = $objectManager->create(Store::class);
$store->load('klevu_test_store_1', 'code');
$store->setCurrentCurrency($store->getDefaultCurrency());
$storeManager = $objectManager->get(StoreManager::class);
$storeManager->setCurrentStore($store);

// create sequence tables.
// core Magento generates these for store ids 0-9 only!!!!!! FFS
// \Magento\TestFramework\Db\Sequence::generateSequences
$entities = [
    'order'
];
$ddlSequence = $objectManager->get(DdlSequence::class);
$appResource = $objectManager->get(AppResource::class);
$connection = $appResource->getConnection();
foreach ($entities as $entityName) {
    $sequenceName = $appResource->getTableName(sprintf('sequence_%s_%s', $entityName, $store->getId()));
    if (!$connection->isTableExists($sequenceName)) {
        $connection->query($ddlSequence->getCreateSequenceDdl($sequenceName));
    }
}

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);

/** @var AddressInterface $quoteShippingAddress */
$quoteShippingAddress = $objectManager->get(AddressInterfaceFactory::class)->create();

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@klevu.com', $store->getWebsiteId());
$addresses = $customer->getAddresses();
$addressKeys = array_keys($addresses);
$customerAddress = isset($addresses[$addressKeys[0]]) ? $addresses[$addressKeys[0]] : null;
if ($customerAddress) {
    $quoteShippingAddress->importCustomerAddressData($customerAddress);
}

/** @var CartInterface $quote */
$quote = $objectManager->get(CartInterfaceFactory::class)->create();
$quote->setStoreId($store->getId());
$quote->setIsActive(true);
$quote->setIsMultiShipping(0);
$quote->assignCustomerWithAddressChange($customer);
$quote->setShippingAddress($quoteShippingAddress);
$quote->setBillingAddress($quoteShippingAddress);
$quote->setCheckoutMethod(Onepage::METHOD_CUSTOMER);
$quote->setReservedOrderId('KLEVU_9876543');
$quote->setEmail($customer->getEmail());
$quote->setBaseCurrencyCode($store->getBaseCurrencyCode());
$quote->setQuoteCurrencyCode($store->getDefaultCurrencyCode());
$quote->setStoreCurrencyCode($store->getDefaultCurrencyCode());

$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getShippingAddress()->collectShippingRates();
$quote->getPayment()->setMethod('checkmo');
$quoteRepository->save($quote);
