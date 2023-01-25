<?php

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$repository = $objectManager->create(CustomerRepositoryInterface::class);

/** @var Store $store */
$store = $objectManager->create(Store::class);
$store->load('klevu_test_store_1', 'code');

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$customerEmails = [
    'customer@klevu.com'
];

foreach ($customerEmails as $customerEmail) {
    try {
        $customer = $repository->get($customerEmail, $store->getWebsiteId());
        $repository->delete($customer);
    } catch (\Exception $e) {
        // this is fine
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
