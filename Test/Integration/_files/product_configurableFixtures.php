<?php

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as IndexerProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as ConfigurableOptionsFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
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
include "productAttributeFixtures.php";

/** @var Website $website1 */
$website1 = $objectManager->create(Website::class);
$website1->load('klevu_test_website_1', 'code');

/** @var Website $website2 */
$website2 = $objectManager->create(Website::class);
$website2->load('klevu_test_website_2', 'code');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var EavConfig $eavConfig */
$eavConfig = $objectManager->get(EavConfig::class);
$configurableAttribute = $eavConfig->getAttribute('catalog_product', 'klevu_test_configurable');
$configurableAttributeOptions = $configurableAttribute->getOptions();

/** @var ConfigurableOptionsFactory $configurableOptionsFactory */
$configurableOptionsFactory = $objectManager->get(ConfigurableOptionsFactory::class);

/** @var IndexerProcessor $indexerProcessor */
$indexerProcessor = $objectManager->get(IndexerProcessor::class);

$fixtures = [
    [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_1',
        'name' => '[Klevu] Simple Child Product 1',
        'description' => '[Klevu Test Fixtures] Simple product 1',
        'short_description' => '[Klevu Test Fixtures] Simple product 1',
        'attribute_set_id' => 4,
        'website_ids' => array_filter([
            $website1->getId(),
            $website2->getId(),
        ]),
        'price' => 20.00,
        'special_price' => null,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Product 1',
        'meta_description' => '[Klevu Test Fixtures] Simple product 1',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu_simple_child_1_' . crc32(rand()),
        'klevu_test_configurable' => $configurableAttributeOptions[1]->getValue(),
    ],
    [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_1',
        'name' => '[Klevu] Configurable Product 1',
        'description' => '[Klevu Test Fixtures] Configurable product 1',
        'short_description' => 'Configurable Product',
        'attribute_set_id' => 4,
        'website_ids' => array_filter([
            $website1->getId(),
            $website2->getId(),
        ]),
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 1',
        'meta_description' => '[Klevu Test Fixtures] Configurable Product 1',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu_configurable_1_' . crc32(rand()),
        'child_skus' => [
            'klevu_simple_child_1'
        ],
    ]
];

// ------------------------------------------------------------------

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$collection = $objectManager->create(ProductResource\Collection::class);
$collection->addAttributeToFilter('sku', ['in' => array_column($fixtures, 'sku')]);
$collection->setFlag('has_stock_status_filter', true);
$collection->load();
foreach ($collection as $product) {
    $productRepository->delete($product);
}

// ------------------------------------------------------------------

$attributeValues = [];
$productSkuToId = [];

// Simple products
foreach ($fixtures as $fixture) {
    if ($fixture['type_id'] !== 'simple') {
        continue;
    }

    /** @var $product Product */
    $product = $objectManager->create(Product::class);
    $product->isObjectNew(true);
    $product->addData($fixture);

    $newProduct = $productRepository->save($product);

    $indexerProcessor->reindexRow($newProduct->getId());

    if (0 === strpos($fixture['sku'], 'klevu_simple_child_')) {
        $attributeValues[$fixture['sku']] = [
            'label' => 'test',
            'attribute_id' => $configurableAttribute->getId(),
            'value_index' => $fixture['klevu_test_configurable'],
        ];
        $productSkuToId[$product->getSku()] = $newProduct->getId();
    }
}

// Configurable Setup
foreach ($fixtures as $fixture) {
    if ($fixture['type_id'] !== 'configurable') {
        continue;
    }

    $childSkus = $fixture['child_skus'];
    unset($fixture['price'], $fixture['special_price'], $fixture['child_skus']);

    /** @var $product Product */
    $product = $objectManager->create(Product::class);

    $values = array_values(array_intersect_key(
        $attributeValues,
        array_fill_keys($childSkus, '')
    ));
    $associatedProductIds = array_values(array_intersect_key(
        $productSkuToId,
        array_fill_keys($childSkus, '')
    ));

    if ($values) {
        $configurableAttributesData = [
            [
                'attribute_id' => $configurableAttribute->getId(),
                'code' => $configurableAttribute->getAttributeCode(),
                'label' => $configurableAttribute->getDataUsingMethod('store_label'),
                'position' => 0,
                'values' => $values,
            ],
        ];
        $configurableOptions = $configurableOptionsFactory->create($configurableAttributesData);
        /** @var ProductExtensionInterface $extensionConfigurableAttributes */
        $extensionConfigurableAttributes = $product->getExtensionAttributes();
        $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
        $extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

        $product->setExtensionAttributes($extensionConfigurableAttributes);
    }

    $product->isObjectNew(true);
    $product->addData($fixture);

    $productRepository->cleanCache();
    $productRepository->save($product);
}

/** @var Manager $moduleManager */
$moduleManager = Bootstrap::getObjectManager()->get(Manager::class);
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
        ->addFilter(\Magento\InventoryApi\Api\Data\SourceItemInterface::SKU, ['klevu_simple_child_1'], 'in')
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
     * klevu_simple_1 - EU-source-1(id:10) - 5.5qty
     * klevu_simple_1 - EU-source-2(id:20) - 3qty
     * klevu_simple_1 - EU-source-3(id:30) - 10qty (out of stock)
     * klevu_simple_1 - EU-source-4(id:40) - 10qty (disabled source)
     */
    $sourcesItemsData = [
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_child_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 3.5,
            \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
        ],
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-2',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_child_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 2,
            \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
        ],
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-3',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_child_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 20,
            \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK,
        ],
        [
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => 'eu-disabled',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'klevu_simple_child_1',
            \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => 15,
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

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
