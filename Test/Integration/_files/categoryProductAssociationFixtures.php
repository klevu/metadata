<?php
/** @noinspection PhpUnhandledExceptionInspection */

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var CategoryCollection $categoryCollection */
$categoryCollection = $objectManager->get(CategoryCollectionFactory::class)->create();
$categoryCollection->addAttributeToFilter('url_key', ['in' => [
    'klevu-test-category-1',
    'klevu-test-category-1-1',
]]);
$categoryCollection->load();

/** @var ProductCollection $productCollection */
$productCollection = $objectManager->get(ProductCollectionFactory::class)->create();
$productCollection->addAttributeToFilter('sku', ['in' => [
    'klevu_simple_1',
    'klevu_simple_2',
    'klevu_simple_3',
    'klevu_simple_4',
    'klevu_simple_5',
    'klevu_configurable_1',
    'klevu_configurable_4',
]]);

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
foreach ($productCollection as $product) {
    $categoryLinkManagement->assignProductToCategories(
        $product->getSku(),
        $categoryCollection->getColumnValues('entity_id')
    );
}

$indexerFactory = $objectManager->get(IndexerFactory::class);
$indexes = [
//    'catalog_product_attribute',
//    'catalog_product_price',
//    'inventory',
//    'cataloginventory_stock',
    'catalog_category_product',
    'catalog_product_category',
    'catalogsearch_fulltext',
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
