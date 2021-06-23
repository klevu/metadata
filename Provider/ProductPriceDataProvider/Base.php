<?php

namespace Klevu\Metadata\Provider\ProductPriceDataProvider;

use Klevu\Metadata\Api\Data\ProductPriceDataInterface;
use Klevu\Metadata\Api\Data\ProductPriceDataInterfaceFactory;
use Klevu\Metadata\Api\ProductPriceDataProviderInterface;
use Klevu\Metadata\Traits\ProductStoreTrait;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Base implements ProductPriceDataProviderInterface
{
    use ProductStoreTrait;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductPriceDataInterfaceFactory
     */
    private $productPriceDataFactory;

    /**
     * Simple constructor.
     * @param StoreManagerInterface $storeManager
     * @param ProductPriceDataInterfaceFactory $productPriceDataFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductPriceDataInterfaceFactory $productPriceDataFactory
    ) {
        $this->storeManager = $storeManager;
        $this->productPriceDataFactory = $productPriceDataFactory;
    }

    /**
     * @param ProductInterface $product
     * @return ProductPriceDataInterface
     * @throws NoSuchEntityException
     */
    public function getPriceDataForProduct(ProductInterface $product)
    {
        /** @var ProductPriceDataInterface $productPriceData */
        $productPriceData = $this->productPriceDataFactory->create();

        $price = $product->getPrice();
        if (is_numeric($price)) {
            $productPriceData->setPrice((float)$price);
        }

        switch (true) {
            case method_exists($product, 'getSpecialPrice'):
                $specialPrice = $product->getSpecialPrice();
                break;

            case $product instanceof DataObject:
                $specialPrice = $product->getDataUsingMethod('special_price');
                break;

            default:
                $specialPrice = null;
                break;
        }
        if (is_numeric($specialPrice)) {
            $productPriceData->setSpecialPrice((float)$specialPrice);
        }

        $store = $this->getStoreForProduct($product, $this->storeManager);
        if (method_exists($store, 'getBaseCurrencyCode')) {
            $productPriceData->setCurrencyCode($store->getBaseCurrencyCode());
        }

        return $productPriceData;
    }
}
