<?php

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

include "customerAddressFixtures_rollback.php";

$objectManager = Bootstrap::getObjectManager();

/** @var Store $store */
$store = $objectManager->create(Store::class);
$store->load('klevu_test_store_1', 'code');

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@klevu.com', $store->getWebsiteId());

/** @var Address $customerAddress */
$customerAddress = $objectManager->create(Address::class);

/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'entity_id' => 1,
        'attribute_set_id' => 2,
        'telephone' => '01234567890',
        'postcode' => 'EC3M 1DT',
        'country_id' => 'GB',
        'city' => 'London',
        'company' => 'Klevu',
        'street' => '123 Fake Street',
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => $customer->getId(),
        'region_id' => 1,
    ]
);
$customerAddress->save();

/** @var AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(AddressRepositoryInterface::class);
$customerAddress = $addressRepository->getById(1);
$customerAddress->setCustomerId($customer->getId());
try {
    $customerAddress = $addressRepository->save($customerAddress);
} catch (\Magento\Framework\Exception\LocalizedException $e) {
    $test = $e;
}

$customerRegistry->remove($customerAddress->getCustomerId());

/** @var AddressRegistry $addressRegistry */
$addressRegistry = $objectManager->get(AddressRegistry::class);
$addressRegistry->remove($customerAddress->getId());
