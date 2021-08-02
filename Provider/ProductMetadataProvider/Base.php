<?php

namespace Klevu\Metadata\Provider\ProductMetadataProvider;

use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Klevu\Metadata\Api\ProductPriceDataProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Klevu\Metadata\Constants;

class Base implements ProductMetadataProviderInterface
{
    /**
     * @var ProductPriceDataProviderInterface
     */
    private $productPriceDataProvider;

    /**
     * Simple constructor.
     * @param ProductPriceDataProviderInterface $productPriceDataProvider
     *
     * @note Keeping constructor intentionally
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
        // @TODO: should be uncommented when KS-6048 is addressed
        //$productPriceData = $this->productPriceDataProvider->getPriceDataForProduct($product);
        //$itemSalePrice = $productPriceData->getPrice();

        return [
            'platform' => Constants::KLEVU_PLATFORM_TYPE,
            'pageType' => static::PAGE_TYPE,
            'itemName' => $product->getName(),
            'itemUrl' => method_exists($product, 'getProductUrl')
                ? $product->getProductUrl()
                : '',
            'itemId' => (string)$product->getId(),
            'itemGroupId' => '',
            /*'itemSalePrice' => (null !== $itemSalePrice)
                ? number_format($itemSalePrice, 2)
                : '',
            'itemCurrency' => $productPriceData->getCurrencyCode(),*/
        ];
    }
}
