<?php

namespace Klevu\Metadata\Provider;

use Klevu\Metadata\Api\CheckoutMetadataProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Psr\Log\LoggerInterface;
use Klevu\Metadata\Constants;

class CheckoutMetadataProvider implements CheckoutMetadataProviderInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * CheckoutMetadataProvider constructor.
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    /**
     * @api
     * @param CartInterface $cart
     * @return array
     */
    public function getMetadataForCart(CartInterface $cart)
    {
        $cartItems = method_exists($cart, 'getAllVisibleItems')
            ? $cart->getAllVisibleItems()
            : [];

        return [
            'platform' => Constants::KLEVU_PLATFORM_TYPE,
            'pageType' => static::PAGE_TYPE,
            'cartRecords' => array_filter(array_map([$this, 'getMetadataForCartItem'], $cartItems)),
        ];
    }

    /**
     * @param CartItemInterface $cartItem
     * @return string[]
     */
    public function getMetadataForCartItem(CartItemInterface $cartItem)
    {
        $return = [
            'itemId' => $this->getItemIdFromCartItem($cartItem),
            'itemGroupId' => '',
        ];

        switch ($cartItem->getProductType()) {
            case ProductTypeConfigurable::TYPE_CODE:
                $itemGroupId = $return['itemId'];
                $return['itemId'] = '';
                $return['itemGroupId'] = $itemGroupId;
                try {
                    $simpleProduct = $this->productRepository->get($cartItem->getSku());
                    $return['itemId'] = $itemGroupId . Constants::ID_SEPARATOR . $simpleProduct->getId();
                } catch (NoSuchEntityException $e) {
                    $this->logger->error($e->getMessage());
                }
                break;

            default:
                break;
        }

        return $return;
    }

    /**
     * @param CartItemInterface $cartItem
     * @return string
     */
    private function getItemIdFromCartItem(CartItemInterface $cartItem)
    {
        $return = '';
        if ($cartItem instanceof DataObject) {
            $return = (string)$cartItem->getDataUsingMethod('product_id');
        } elseif (method_exists($cartItem, 'getProductId')) {
            $return = (string)$cartItem->getProductId();
        }

        if (!$return) {
            try {
                $product = $this->productRepository->get($cartItem->getSku());
                $return = (string)$product->getId();
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage(), ['originalException' => $e]);
            }
        }

        return $return;
    }
}
