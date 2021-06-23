<?php

namespace Klevu\Metadata\Api;

use Klevu\Metadata\Api\Data\ProductPriceDataInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface ProductPriceDataProviderInterface
{
    /**
     * @param ProductInterface $product
     * @return ProductPriceDataInterface|null
     * @throws NoSuchEntityException
     */
    public function getPriceDataForProduct(ProductInterface $product);
}
