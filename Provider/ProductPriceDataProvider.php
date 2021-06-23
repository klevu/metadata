<?php

namespace Klevu\Metadata\Provider;

use Klevu\Metadata\Api\Data\ProductPriceDataInterface;
use Klevu\Metadata\Api\ProductPriceDataProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductPriceDataProvider implements ProductPriceDataProviderInterface
{
    /**
     * @var ProductPriceDataProviderInterface[]
     */
    private $productTypePriceDataProviders = [];

    /**
     * ProductPriceDataProvider constructor.
     * @param ProductPriceDataProviderInterface[]|null $productTypePriceDataProviders
     */
    public function __construct(
        array $productTypePriceDataProviders = null
    ) {
        if (null !== $productTypePriceDataProviders) {
            array_walk(
                $productTypePriceDataProviders,
                function (ProductPriceDataProviderInterface $productPriceDataProvider, $productType) {
                    $this->productTypePriceDataProviders[$productType] = $productPriceDataProvider;
                }
            );
        }
    }

    /**
     * @param ProductInterface $product
     * @return ProductPriceDataInterface|null
     * @throws NoSuchEntityException
     */
    public function getPriceDataForProduct(ProductInterface $product)
    {
        $typeId = $product->getTypeId();

        return isset($this->productTypePriceDataProviders[$typeId])
            ? $this->productTypePriceDataProviders[$typeId]->getPriceDataForProduct($product)
            : null;
    }
}
