<?php

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$orderIds = [
    'KLEVU_9876543'
];

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);


/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);

$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter('reserved_order_id', $orderIds, 'in');
$searchCriteria = $searchCriteriaBuilder->create();
$results = $quoteRepository->getList($searchCriteria);
$quotes = $results->getItems();

foreach ($quotes as $quote) {
    $quoteRepository->delete($quote);
}

/** @var OrderRepositoryInterface $quoteRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);

$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter('increment_id', $orderIds, 'in');
$searchCriteria = $searchCriteriaBuilder->create();
$results = $orderRepository->getList($searchCriteria);
$orders = $results->getItems();

foreach ($orders as $order) {
    $orderRepository->delete($order);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

include "customerAddressFixtures_rollback.php";
include "customerFixtures_rollback.php";
include "currencyRateFixtures_rollback.php";
