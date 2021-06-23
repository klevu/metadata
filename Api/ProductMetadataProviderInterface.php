<?php

namespace Klevu\Metadata\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface ProductMetadataProviderInterface
{
    const PAGE_TYPE = 'pdp';

    /**
     * @api
     * @param ProductInterface $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMetadataForProduct(ProductInterface $product);
}
