<?php

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var StockInterfaceFactory $stockFactory */
$stockFactory = Bootstrap::getObjectManager()->get(StockInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var StockRepositoryInterface $stockRepository */
$stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);

$stocksData = [
    [
        // define only required and needed for tests fields
        StockInterface::STOCK_ID => 10,
        StockInterface::NAME => 'EU-stock',
        ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => [
            'sales_channels' => [
                [
                    SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    SalesChannelInterface::CODE => 'klevu_test_website_1',
                ]
            ],
        ],
    ],
    [
        StockInterface::STOCK_ID => 20,
        StockInterface::NAME => 'US-stock'
    ],
    [
        StockInterface::STOCK_ID => 30,
        StockInterface::NAME => 'Global-stock',
        ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => [
            'sales_channels' => [
                [
                    SalesChannelInterface::TYPE => SalesChannelInterface::TYPE_WEBSITE,
                    SalesChannelInterface::CODE => 'klevu_test_website_2',
                ],
            ],
        ],
    ],
];
foreach ($stocksData as $stockData) {
    $stock = $stockFactory->create();
    $dataObjectHelper->populateWithArray($stock, $stockData, StockInterface::class);
    $stockRepository->save($stock);
}
