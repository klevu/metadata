<?php

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;

$skusToDelete = [
    'klevu_configurable_1',
    'klevu_simple_child_1',
];

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var Manager $moduleManager */
$moduleManager = Bootstrap::getObjectManager()->get(Manager::class);
// soft dependency in tests because we don't have possibility replace fixture from different modules
if ($moduleManager->isEnabled('Magento_InventoryCatalog')) {
    include "stockSourceLinkFixtures_rollback.php";
    include "sourcesFixtures_rollback.php";
    include "stocksFixtures_rollback.php";

    /** @var \Magento\InventoryApi\Api\SourceItemRepositoryInterface $sourceItemRepository */
    $sourceItemRepository = $objectManager->get(\Magento\InventoryApi\Api\SourceItemRepositoryInterface::class);
    /** @var \Magento\InventoryApi\Api\SourceItemsDeleteInterface $sourceItemsDelete */
    $sourceItemsDelete = $objectManager->get(\Magento\InventoryApi\Api\SourceItemsDeleteInterface::class);
    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);

    $searchCriteria = $searchCriteriaBuilder->addFilter(
        \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU,
        $skusToDelete,
        'in'
    )->create();
    $sourceItems = $sourceItemRepository->getList($searchCriteria)->getItems();

    /**
     * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
     * In that case there is "if" which checks that SKU1, SKU2 and SKU3 still exists in database.
     */
    if (!empty($sourceItems)) {
        $sourceItemsDelete->execute($sourceItems);
    }
}

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

foreach ($skusToDelete as $skuToDelete) {
    try {
        $product = $productRepository->get($skuToDelete);
        $productRepository->delete($product);
    } catch (NoSuchEntityException $e) {
        // This is fine
    }
}

if ($moduleManager->isEnabled('Magento_InventoryCatalog')) {
    // this is ugly, but Magento doesn't give us a repository to access this data
    $resource = $objectManager->get(ResourceConnection::class);
    $connection = $resource->getConnection();
    $reservationTable = $resource->getTableName('inventory_reservation');

    $condition = [\Magento\InventoryReservationsApi\Model\ReservationInterface::SKU . ' IN (?)' => $skusToDelete];
    $connection->delete($reservationTable, $condition);
}

$indexerFactory = $objectManager->get(IndexerFactory::class);
$indexes = [
    'inventory',
    'cataloginventory_stock',
];
foreach ($indexes as $index) {
    $indexer = $indexerFactory->create();
    try {
        $indexer->load($index);
        $indexer->reindexAll();
    } catch (\InvalidArgumentException $e) {
        // Support for older versions of Magento which may not have all indexers
        continue;
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
