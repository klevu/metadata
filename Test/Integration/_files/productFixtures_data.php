<?php
/** @noinspection PhpUnhandledExceptionInspection */

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultStoreView = $storeManager->getDefaultStoreView();

/** @var EavConfig $eavConfig */
$eavConfig = $objectManager->get(EavConfig::class);

$configurableAttribute = $eavConfig->getAttribute('catalog_product', 'klevu_test_configurable');
$configurableAttributeOptions = $configurableAttribute->getOptions();

$fixtures = [];

// Standalone Simple
$fixtures[] = [
    'type_id' => 'simple',
    'sku' => 'klevu_simple_1',
    'name' => '[Klevu] Simple Product 1',
    'description' => '[Klevu Test Fixtures] Simple product 1 (Enabled; Visibility Both)',
    'short_description' => '[Klevu Test Fixtures] Simple product 1',
    'attribute_set_id' => 4,
    'website_ids' => [
        $defaultStoreView->getWebsiteId(),
    ],
    'price' => 10.00,
    'special_price' => 4.99,
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
    'url_key' => 'klevu-simple-product-1',
];
$fixtures[] = [
    'type_id' => 'simple',
    'sku' => 'klevu_simple_2',
    'name' => '[Klevu] Simple Product 2',
    'description' => '[Klevu Test Fixtures] Simple product 2 (Enabled; Visibility None)',
    'short_description' => '[Klevu Test Fixtures] Simple product 2',
    'attribute_set_id' => 4,
    'website_ids' => [
        $defaultStoreView->getWebsiteId(),
    ],
    'price' => 20.20,
    'weight' => 1,
    'tax_class_id' => 2,
    'meta_title' => '[Klevu] Simple Product 2',
    'meta_description' => '[Klevu Test Fixtures] Simple product 2',
    'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
    'status' => Status::STATUS_ENABLED,
    'stock_data' => [
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ],
    'url_key' => 'klevu-simple-product-2',
];
$fixtures[] = [
    'type_id' => 'simple',
    'sku' => 'klevu_simple_3',
    'name' => '[Klevu] Simple Product 3',
    'description' => '[Klevu Test Fixtures] Simple product 3 (Enabled; Visibility Catalog)',
    'short_description' => '[Klevu Test Fixtures] Simple product 3',
    'attribute_set_id' => 4,
    'website_ids' => [
        $defaultStoreView->getWebsiteId(),
    ],
    'price' => 30.33,
    'weight' => 1,
    'tax_class_id' => 2,
    'meta_title' => '[Klevu] Simple Product 3',
    'meta_description' => '[Klevu Test Fixtures] Simple product 3',
    'visibility' => Visibility::VISIBILITY_IN_CATALOG,
    'status' => Status::STATUS_ENABLED,
    'stock_data' => [
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ],
    'url_key' => 'klevu-simple-product-3',
];
$fixtures[] = [
    'type_id' => 'simple',
    'sku' => 'klevu_simple_4',
    'name' => '[Klevu] Simple Product 4',
    'description' => '[Klevu Test Fixtures] Simple product 4 (Enabled; Visibility Search)',
    'short_description' => '[Klevu Test Fixtures] Simple product 4',
    'attribute_set_id' => 4,
    'website_ids' => [
        $defaultStoreView->getWebsiteId(),
    ],
    'price' => 40.50,
    'weight' => 1,
    'tax_class_id' => 2,
    'meta_title' => '[Klevu] Simple Product 4',
    'meta_description' => '[Klevu Test Fixtures] Simple product 4',
    'visibility' => Visibility::VISIBILITY_IN_SEARCH,
    'status' => Status::STATUS_ENABLED,
    'stock_data' => [
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ],
    'url_key' => 'klevu-simple-product-4',
];
$fixtures[] = [
    'type_id' => 'simple',
    'sku' => 'klevu_simple_5',
    'name' => '[Klevu] Simple Product 5',
    'description' => '[Klevu Test Fixtures] Simple product 5 (Disabled; Visibility Both)',
    'short_description' => '[Klevu Test Fixtures] Simple product 5',
    'attribute_set_id' => 4,
    'website_ids' => [
        $defaultStoreView->getWebsiteId(),
    ],
    'price' => 50.99,
    'weight' => 1,
    'tax_class_id' => 2,
    'meta_title' => '[Klevu] Simple Product 5',
    'meta_description' => '[Klevu Test Fixtures] Simple product 5',
    'visibility' => Visibility::VISIBILITY_BOTH,
    'status' => Status::STATUS_DISABLED,
    'stock_data' => [
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ],
    'url_key' => 'klevu-simple-product-5',
];

if (count($configurableAttributeOptions)) {
    // Configurable
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_1',
        'name' => '[Klevu] Simple Child Product 1',
        'description' => '[Klevu Test Fixtures] Simple product 1 (Enabled; Visibility None)',
        'short_description' => '[Klevu Test Fixtures] Simple product 1',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 99.99,
        'special_price' => 49.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 1',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 1',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-simple-child-product-1',
        'klevu_test_configurable' => $configurableAttributeOptions[1]->getValue(),
    ];
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_2',
        'name' => '[Klevu] Simple Child Product 2 [OOS]',
        'description' => '[Klevu Test Fixtures] Simple product 2 (Enabled; Visibility None; OOS)',
        'short_description' => '[Klevu Test Fixtures] Simple child product 2',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 89.99,
        'special_price' => 39.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 2',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 2',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 0,
        ],
        'url_key' => 'klevu-simple-child-product-2',
        'klevu_test_configurable' => $configurableAttributeOptions[2]->getValue(),
    ];
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_3',
        'name' => '[Klevu] Simple Child Product 3',
        'description' => '[Klevu Test Fixtures] Simple child product 3 (Disabled; Visibility None)',
        'short_description' => '[Klevu Test Fixtures] Simple child product 3',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 79.99,
        'special_price' => 29.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 3',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 3',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_DISABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-simple-child-product-3',
        'klevu_test_configurable' => $configurableAttributeOptions[3]->getValue(),
    ];
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_4',
        'name' => '[Klevu] Simple Child Product 4',
        'description' => '[Klevu Test Fixtures] Simple child product 4 (Enabled; Visibility None)',
        'short_description' => '[Klevu Test Fixtures] Simple child product 4',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 99.99,
        'special_price' => 69.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 4',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 4',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-simple-child-product-4',
        'klevu_test_configurable' => $configurableAttributeOptions[4]->getValue(),
    ];
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_5',
        'name' => '[Klevu] Simple Child Product 5',
        'description' => '[Klevu Test Fixtures] Simple child product 5 (Enabled; Visibility None)',
        'short_description' => '[Klevu Test Fixtures] Simple child product 5',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 9.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 5',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 5',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-simple-child-product-5',
        'klevu_test_configurable' => $configurableAttributeOptions[5]->getValue(),
    ];
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_6',
        'name' => '[Klevu] Simple Child Product 6',
        'description' => '[Klevu Test Fixtures] Simple child product 6 (Enabled; Visibility None)',
        'short_description' => '[Klevu Test Fixtures] Simple child product 6',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 89.99,
        'special_price' => 39.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 6',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 6',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-simple-child-product-6',
        'klevu_test_configurable' => $configurableAttributeOptions[6]->getValue(),
    ];
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_7',
        'name' => '[Klevu] Simple Child Product 7',
        'description' => '[Klevu Test Fixtures] Simple child product 7 (Enabled; Visibility None)',
        'short_description' => '[Klevu Test Fixtures] Simple child product 7',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 109.99,
        'special_price' => 39.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 7',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 7',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-simple-child-product-7',
        'klevu_test_configurable' => $configurableAttributeOptions[7]->getValue(),
    ];
    $fixtures[] = [
        'type_id' => 'simple',
        'sku' => 'klevu_simple_child_8',
        'name' => '[Klevu] Simple Child Product 8',
        'description' => '[Klevu Test Fixtures] Simple child product 8 (Enabled; Visibility None)',
        'short_description' => '[Klevu Test Fixtures] Simple child product 8',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 79.99,
        'weight' => 1,
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Simple Child Product 8',
        'meta_description' => '[Klevu Test Fixtures] Simple child product 8',
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-simple-child-product-8',
        'klevu_test_configurable' => $configurableAttributeOptions[8]->getValue(),
    ];
    $fixtures[] =
        [
            'type_id' => 'configurable',
            'sku' => 'klevu_configurable_1',
            'name' => '[Klevu] Configurable Product 1',
            'description' => '[Klevu Test Fixtures] Configurable product 1',
            'short_description' => 'No children',
            'attribute_set_id' => 4,
            'website_ids' => [
                $defaultStoreView->getWebsiteId(),
            ],
            'tax_class_id' => 2,
            'meta_title' => '[Klevu] Configurable Product 1',
            'meta_description' => '[Klevu Test Fixtures] Configurable product 1',
            'visibility' => Visibility::VISIBILITY_BOTH,
            'status' => Status::STATUS_ENABLED,
            'stock_data' => [
                'use_config_manage_stock' => 1,
                'is_in_stock' => 1,
            ],
            'url_key' => 'klevu-configurable-product-1',
            'child_skus' => [],
        ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_2',
        'name' => '[Klevu] Configurable Product 2',
        'description' => '[Klevu Test Fixtures] Configurable product 2',
        'short_description' => 'No available children',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 2',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 2',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-2',
        'child_skus' => [
            'klevu_simple_child_2',
            'klevu_simple_child_3',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_3',
        'name' => '[Klevu] Configurable Product 3',
        'description' => '[Klevu Test Fixtures] Configurable product 3',
        'short_description' => '1 child; no special price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 3',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 3',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-3',
        'child_skus' => [
            'klevu_simple_child_5',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_4',
        'name' => '[Klevu] Configurable Product 4',
        'description' => '[Klevu Test Fixtures] Configurable product 4',
        'short_description' => '1 child; with special price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 4',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 4',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-4',
        'child_skus' => [
            'klevu_simple_child_1',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_5',
        'name' => '[Klevu] Configurable Product 5',
        'description' => '[Klevu Test Fixtures] Configurable product 5',
        'short_description' => '2 children; 1 OOS with lower price and special price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 5',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 5',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-5',
        'child_skus' => [
            'klevu_simple_child_1',
            'klevu_simple_child_2',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_6',
        'name' => '[Klevu] Configurable Product 6',
        'description' => '[Klevu Test Fixtures] Configurable product 6',
        'short_description' => '2 children; 1 disabled with lower price and special price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 6',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 6',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-6',
        'child_skus' => [
            'klevu_simple_child_1',
            'klevu_simple_child_3',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_7',
        'name' => '[Klevu] Configurable Product 7',
        'description' => '[Klevu Test Fixtures] Configurable product 7',
        'short_description' => '2 children; same price, one lower special price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 7',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 7',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-7',
        'child_skus' => [
            'klevu_simple_child_1',
            'klevu_simple_child_4',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_8',
        'name' => '[Klevu] Configurable Product 8',
        'description' => '[Klevu Test Fixtures] Configurable product 8',
        'short_description' => '2 children; one lower price and special price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 8',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 8',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-8',
        'child_skus' => [
            'klevu_simple_child_1',
            'klevu_simple_child_6',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_9',
        'name' => '[Klevu] Configurable Product 9',
        'description' => '[Klevu Test Fixtures] Configurable product 9',
        'short_description' => '2 children; one higher price and lower special price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 9',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 9',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-9',
        'child_skus' => [
            'klevu_simple_child_1',
            'klevu_simple_child_7',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_10',
        'name' => '[Klevu] Configurable Product 10',
        'description' => '[Klevu Test Fixtures] Configurable product 10',
        'short_description' => '2 children; one no special price, price in middle',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 10',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 10',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-10',
        'child_skus' => [
            'klevu_simple_child_1',
            'klevu_simple_child_8',
        ],
    ];
    $fixtures[] = [
        'type_id' => 'configurable',
        'sku' => 'klevu_configurable_11',
        'name' => '[Klevu] Configurable Product 11',
        'description' => '[Klevu Test Fixtures] Configurable product 11',
        'short_description' => '2 children; one no special price, lower price',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'meta_title' => '[Klevu] Configurable Product 11',
        'meta_description' => '[Klevu Test Fixtures] Configurable product 11',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-configurable-product-11',
        'child_skus' => [
            'klevu_simple_child_1',
            'klevu_simple_child_5',
        ]
    ];
    $fixtures[] = [
        'type_id' => 'grouped',
        'sku' => 'klevu_grouped_1',
        'name' => '[Klevu] Grouped Product 1',
        'description' => '[Klevu Test Fixtures] Grouped Product 1 Description',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'tax_class_id' => 2,
        'url_key' => 'klevu-grouped-product-1',
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'associated_skus' => [
            'klevu_simple_child_1'
        ]
    ];
    $fixtures[] = [
        'type_id' => 'bundle',
        'sku' => 'klevu_bundle_1',
        'name' => '[Klevu] Bundle Product 1',
        'description' => '[Klevu Test Fixtures] Bundle Product 1 Description',
        'attribute_set_id' => 4,
        'website_ids' => [
            $defaultStoreView->getWebsiteId(),
        ],
        'price' => 100.00,
        'special_price' => 49.99,
        'weight' => 12,
        'meta_title' => '[Klevu] Bundle Product Test',
        'meta_description' => '[Klevu Test Fixtures] assigned bundle product',
        'tax_class_id' => 2,
        'stock_data' => [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ],
        'url_key' => 'klevu-bundle-product-test-' . crc32(rand()),
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'associated_skus' => [
            'klevu_simple_child_1'
        ]
    ];
}
