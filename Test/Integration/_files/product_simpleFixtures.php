<?php

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as IndexerProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Manager $moduleManager */
$moduleManager = $objectManager->get(Manager::class);

// soft dependency in tests because we don't have possibility replace fixture from different modules
if ($moduleManager->isEnabled('Magento_InventoryCatalog')) {
    include "stocksFixtures.php";
    include "sourcesFixtures.php";
    include "stockSourceLinkFixtures.php";
}

/** @var Website $website1 */
$website1 = $objectManager->create(Website::class);
$website1->load('klevu_test_website_1', 'code');

/** @var Website $website2 */
$website2 = $objectManager->create(Website::class);
$website2->load('klevu_test_website_2', 'code');

/** @var IndexerProcessor $indexerProcessor */
$indexerProcessor = $objectManager->get(IndexerProcessor::class);

$fixtures = [
    [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_1',
        'name' => '[Klevu] Simple Product 1',
        'description' => '[Klevu Test Fixtures] Simple product 1',
        'short_description' => '[Klevu Test Fixtures] Simple product 1',
        'attribute_set_id' => 4,
        'website_ids' => array_filter([
            $website1->getId(),
            $website2->getId(),
        ]),
        'price' => 10.00,
        'special_price' => null,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Product 1',
        'meta_description' => '[Klevu Test Fixtures] Simple product 1',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu_simple_1_' . crc32(rand()),
    ]
];

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

foreach ($fixtures as $fixture) {
    /** @var $product Product */
    $product = $objectManager->create(Product::class);
    $product->isObjectNew(true);
    $product->addData($fixture);

    $newProduct = $productRepository->save($product);

    $indexerProcessor->reindexRow($newProduct->getId());
}

// soft dependency in tests because we don't have possibility replace fixture from different modules
if ($moduleManager->isEnabled('Magento_InventoryCatalog')) {
    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
    /** @var \Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface $defaultSourceProvider */
    $defaultSourceProvider = $objectManager->get(\Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface::class);
    /** @var \Magento\InventoryApi\Api\SourceItemRepositoryInterface $sourceItemRepository */
    $sourceItemRepository = $objectManager->get(\Magento\InventoryApi\Api\SourceItemRepositoryInterface::class);
    /** @var \Magento\InventoryApi\Api\SourceItemsDeleteInterface $sourceItemsDelete */
    $sourceItemsDelete = $objectManager->get(\Magento\InventoryApi\Api\SourceItemsDeleteInterface::class);

    // Unassign created product from default Source
    $searchCriteria = $searchCriteriaBuilder
        ->addFilter(\Magento\InventoryApi\Api\Data\SourceItemInterface::SKU, ['klevu_simple_1'], 'in')
        ->addFilter(\Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE, $defaultSourceProvider->getCode())
        ->create();
    $sourceItems = $sourceItemRepository->getList($searchCriteria)->getItems();
    if (count($sourceItems)) {
        $sourceItemsDelete->execute($sourceItems);
    }

    /** @var DataObjectHelper $dataObjectHelper */
    $dataObjectHelper = $objectManager->get(DataObjectHelper::class);
    /** @var \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory */
    $sourceItemFactory = $objectManager->get(\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory::class);
    /** @var  \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSave */
    $sourceItemsSave = $objectManager->get(\Magento\InventoryApi\Api\SourceItemsSaveInterface::class);

    /**
     * klevu_simple_1 - EU-source-1(id:10) - 50.5qty
     * klevu_simple_1 - EU-source-2(id:20) - 30qty
     * klevu_simple_1 - EU-source-3(id:30) - 100qty (out of stock)
     * klevu_simple_1 - EU-source-4(id:40) - 100qty (disabled source)
     */
    $sourcesItemsData = [
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 5.5,
            \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
        ],
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-2',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 3,
            \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
        ],
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-3',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 10,
            \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK,
        ],
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-disabled',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 10,
            \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
        ]
    ];

    $sourceItems = [];
    foreach ($sourcesItemsData as $sourceItemData) {
        /** @var \Magento\InventoryApi\Api\Data\SourceItemInterface $source */
        $sourceItem = $sourceItemFactory->create();
        $dataObjectHelper->populateWithArray(
            $sourceItem, 
            $sourceItemData, 
            \Magento\InventoryApi\Api\Data\SourceItemInterface::class
        );
        $sourceItems[] = $sourceItem;
    }
    $sourceItemsSave->execute($sourceItems);
}

$indexerFactory = $objectManager->get(IndexerFactory::class);
$indexes = [
    'catalog_product_attribute',
    'catalog_product_price',
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

$productRepository->cleanCache();
