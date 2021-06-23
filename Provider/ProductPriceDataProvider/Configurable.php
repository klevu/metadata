<?php

namespace Klevu\Metadata\Provider\ProductPriceDataProvider;

use Klevu\Metadata\Api\Data\ProductPriceDataInterface;
use Klevu\Metadata\Api\Data\ProductPriceDataInterfaceFactory;
use Klevu\Metadata\Api\ProductPriceDataProviderInterface;
use Klevu\Metadata\Traits\ProductStoreTrait;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPriceInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Store\Model\StoreManagerInterface;

class Configurable implements ProductPriceDataProviderInterface
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
     * @var LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var bool[]
     */
    private $productHasAvailableChildren = [];

    /**
     * Configurable constructor.
     * @param StoreManagerInterface $storeManager
     * @param ProductPriceDataInterfaceFactory $productPriceDataFactory
     * @param LinkManagementInterface $linkManagement
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductPriceDataInterfaceFactory $productPriceDataFactory,
        LinkManagementInterface $linkManagement
    ) {
        $this->storeManager = $storeManager;
        $this->productPriceDataFactory = $productPriceDataFactory;
        $this->linkManagement = $linkManagement;
    }

    /**
     * @param ProductInterface $product
     * @return ProductPriceDataInterface|null
     * @throws NoSuchEntityException
     */
    public function getPriceDataForProduct(ProductInterface $product)
    {
        if (ProductTypeConfigurable::TYPE_CODE !== $product->getTypeId()) {
            throw new \InvalidArgumentException(sprintf(
                'Product argument passed to %s must be of type %s; %s encountered',
                __METHOD__,
                ProductTypeConfigurable::TYPE_CODE,
                $product->getTypeId()
            ));
        }

        $productPriceData = $this->productPriceDataFactory->create();

        if ($this->productHasAvailableChildren($product)) {
            $price = $this->getPrice($product);
            if (null !== $price) {
                $productPriceData->setPrice($price);
            }

            $specialPrice = $this->getSpecialPrice($product);
            if (null !== $specialPrice && ($price && $specialPrice < $price)) {
                $productPriceData->setSpecialPrice($specialPrice);
            }
        }

        $store = $this->getStoreForProduct($product, $this->storeManager);
        if (method_exists($store, 'getBaseCurrencyCode')) {
            $productPriceData->setCurrencyCode($store->getBaseCurrencyCode());
        }

        return $productPriceData;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    private function productHasAvailableChildren(ProductInterface $product)
    {
        if (!isset($this->productHasAvailableChildren[$product->getSku()])) {
            $this->productHasAvailableChildren[$product->getSku()] = false;

            $childProducts = $this->linkManagement->getChildren($product->getSku());
            foreach ($childProducts as $childProduct) {
                if (Status::STATUS_DISABLED === $childProduct->getStatus()) {
                    continue;
                }

                $this->productHasAvailableChildren[$product->getSku()] = true;
            }
        }

        return $this->productHasAvailableChildren[$product->getSku()];
    }

    /**
     * @param ProductInterface $product
     * @return float|null
     */
    private function getPrice(ProductInterface $product)
    {
        $priceInfo = $this->getPriceInfoForProduct($product);
        if (!$priceInfo) {
            throw new \LogicException(sprintf(
                'Could not retrieve priceInfo information for product. Sku: %s; Product class: %s',
                $product->getSku(),
                get_class($product)
            ));
        }

        $basePrice = $priceInfo->getPrice('regular_price');
        if (!($basePrice instanceof ConfigurableRegularPriceInterface)) {
            throw new \LogicException(sprintf(
                'PriceInfo for configurable product expected to be instance of %s; %s encountered',
                ConfigurableRegularPriceInterface::class,
                get_class($basePrice)
            ));
        }

        $minRegularAmount = $basePrice->getMinRegularAmount();
        $price = $minRegularAmount->getValue();

        return is_numeric($price) ? (float)$price : null;
    }

    /**
     * @param ProductInterface $product
     * @return float|null
     */
    private function getSpecialPrice(ProductInterface $product)
    {
        switch (true) {
            case $product instanceof DataObject:
                $finalPrice = $product->getDataUsingMethod('final_price');
                break;

            case method_exists($product, 'getFinalPrice'):
                $finalPrice = $product->getFinalPrice();
                break;

            default:
                $finalPrice = null;
                break;
        }

        return is_numeric($finalPrice) ? (float)$finalPrice : null;
    }

    /**
     * @param ProductInterface $product
     * @return PriceInfoInterface|null
     */
    private function getPriceInfoForProduct(ProductInterface $product)
    {
        $priceInfo = null;
        if (method_exists($product, 'getPriceInfo')) {
            $priceInfo = $product->getPriceInfo();
        }

        if (!($priceInfo instanceof PriceInfoInterface)) {
            $priceInfo = null;
        }

        return $priceInfo;
    }
}
