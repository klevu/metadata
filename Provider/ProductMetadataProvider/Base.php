<?php

namespace Klevu\Metadata\Provider\ProductMetadataProvider;

use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Klevu\Metadata\Api\ProductPriceDataProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Base implements ProductMetadataProviderInterface
{
    /**
     * @var ProductPriceDataProviderInterface
     */
    private $productPriceDataProvider;

    /**
     * Simple constructor.
     * @param ProductPriceDataProviderInterface $productPriceDataProvider
     */
    public function __construct(
        ProductPriceDataProviderInterface $productPriceDataProvider
    ) {
        $this->productPriceDataProvider = $productPriceDataProvider;
    }

    /**
     * @param ProductInterface $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMetadataForProduct(ProductInterface $product)
    {
        $productPriceData = $this->productPriceDataProvider->getPriceDataForProduct($product);
        $itemSalePrice = $productPriceData->getPrice();

        return [
            'pageType' => static::PAGE_TYPE,
            'itemName' => $product->getName(),
            'itemUrl' => method_exists($product, 'getProductUrl')
                ? $product->getProductUrl()
                : '',
            'itemId' => (string)$product->getId(),
            'itemGroupId' => '',
            'itemSalePrice' => (null !== $itemSalePrice)
                ? number_format($itemSalePrice, 2)
                : '',
            'itemCurrency' => $productPriceData->getCurrencyCode(),
        ];
    }
}
