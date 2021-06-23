<?php

namespace Klevu\Metadata\Provider\ProductMetadataProvider;

use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Klevu\Metadata\Api\ProductPriceDataProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Configurable implements ProductMetadataProviderInterface
{
    /**
     * @var ProductPriceDataProviderInterface
     */
    private $productPriceDataProvider;

    /**
     * @var LinkManagementInterface
     */
    private $linkManagementService;

    /**
     * @var bool
     */
    private $useFirstChildProductId = true;

    /**
     * Configurable constructor.
     * @param ProductPriceDataProviderInterface $productPriceDataProvider
     * @param LinkManagementInterface $linkManagementService
     * @param bool|null $useFirstChildProductId
     */
    public function __construct(
        ProductPriceDataProviderInterface $productPriceDataProvider,
        LinkManagementInterface $linkManagementService,
        $useFirstChildProductId = null
    ) {
        $this->productPriceDataProvider = $productPriceDataProvider;
        $this->linkManagementService = $linkManagementService;
        if (null !== $useFirstChildProductId) {
            $this->useFirstChildProductId = (bool)$useFirstChildProductId;
        }
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
            'itemId' => $this->getFirstChildProductId($product),
            'itemGroupId' => (string)$product->getId(),
            'itemSalePrice' => (null !== $itemSalePrice)
                ? number_format($itemSalePrice, 2)
                : '',
            'itemCurrency' => $productPriceData->getCurrencyCode(),
        ];
    }

    /**
     * @param ProductInterface $parentProduct
     * @return string
     */
    private function getFirstChildProductId(ProductInterface $parentProduct)
    {
        if (!$this->useFirstChildProductId) {
            return '';
        }

        $childProducts = $this->linkManagementService->getChildren($parentProduct->getSku());

        $return = '';
        foreach ($childProducts as $childProduct) {
            if (method_exists($childProduct, 'isAvailable') && $childProduct->isAvailable()) {
                $return = (string)$childProduct->getId();
                break;
            }
        }

        return $return;
    }
}
