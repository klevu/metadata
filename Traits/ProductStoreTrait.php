<?php

namespace Klevu\Metadata\Traits;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

trait ProductStoreTrait
{
    /**
     * @param ProductInterface $product
     * @param StoreManagerInterface $storeManager
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStoreForProduct(
        ProductInterface $product,
        StoreManagerInterface $storeManager
    ) {
        switch (true) {
            case method_exists($product, 'getStoreId'):
                $storeId = $product->getStoreId();
                break;

            case $product instanceof DataObject:
                $storeId = $product->getDataUsingMethod('store_id');
                break;

            default:
                $storeId = null;
                break;
        }

        return $storeManager->getStore(is_numeric($storeId) ? (int)$storeId : null);
    }
}
