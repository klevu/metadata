<?php

namespace Klevu\Metadata\Provider\ProductMetadataProvider;

use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Klevu\Metadata\Api\ProductPriceDataProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Klevu\Metadata\Constants;

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
     *
     * @note Keeping productPriceDataProvider argument intentionally
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
        // @TODO: should be uncommented when KS-6048 is addressed
        //$productPriceData = $this->productPriceDataProvider->getPriceDataForProduct($product);
        //$itemSalePrice = $productPriceData->getPrice();

        $itemId = $this->getFirstChildProductId($product);
        $itemGroupId = (string)$product->getId();
        return [
            'platform' => Constants::KLEVU_PLATFORM_TYPE,
            'pageType' => static::PAGE_TYPE,
            'itemName' => $product->getName(),
            'itemUrl' => method_exists($product, 'getProductUrl')
                ? $product->getProductUrl()
                : '',
            'itemId' =>  $this->getKlevuProductId($itemId, $itemGroupId),
            'itemGroupId' => $itemGroupId,
            /*'itemSalePrice' => (null !== $itemSalePrice)
                ? number_format($itemSalePrice, 2)
                : '',
            'itemCurrency' => $productPriceData->getCurrencyCode(),*/
        ];
    }

    /**
     * Generate a Klevu product ID for the given product.
     *
     * @param $product_id
     * @param int $parent_id
     * @return string
     */
    private function getKlevuProductId($product_id, $parent_id = 0)
    {
        if (empty($parent_id)) {
            return (string)$product_id;
        }
        if (!empty($product_id)) {
            $parent_id .= Constants::ID_SEPARATOR;
        } else {
            $parent_id = "";
        }

        return sprintf("%s%s", $parent_id, $product_id);
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
