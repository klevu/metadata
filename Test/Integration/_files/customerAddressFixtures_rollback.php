<?php

/** @var Registry $registry */

use Magento\Customer\Model\Address;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
    
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Address $customerAddress */
$customerAddress = $objectManager->create(Address::class);
$customerAddress->load(1);
if ($customerAddress->getId()) {
    $customerAddress->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
