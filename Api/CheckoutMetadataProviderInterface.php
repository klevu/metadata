<?php

namespace Klevu\Metadata\Api;

use Magento\Quote\Api\Data\CartInterface;

interface CheckoutMetadataProviderInterface
{
    const PAGE_TYPE = 'cart';

    /**
     * @api
     * @param CartInterface $cart
     * @return array
     */
    public function getMetadataForCart(CartInterface $cart);
}
