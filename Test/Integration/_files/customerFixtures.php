<?php

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

include "customerFixtures_rollback.php";

$objectManager = Bootstrap::getObjectManager();
/** @var Customer $customer */
$customer = $objectManager->create(Customer::class);
$customerRegistry = $objectManager->get(CustomerRegistry::class);

/** @var Store $store */
$store = $objectManager->create(Store::class);
$store->load('klevu_test_store_1', 'code');
$websiteId = $store->getWebsiteId();

$customer->setWebsiteId($websiteId);
$customer->setEmail('customer@klevu.com');
$customer->setPassword('password');
$customer->setGroupId(1);
$customer->setStoreId($store->getId());
$customer->setIsActive(1);
$customer->setFirstname('John');
$customer->setLastname('Smith');
$customer->setDefaultBilling(1);
$customer->setDefaultShipping(1);
$customer->isObjectNew(true);
$customer->save();

$customerRegistry->remove($customer->getId());
