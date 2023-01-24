<?php

namespace Klevu\Metadata\Api;

use Magento\Sales\Api\Data\OrderInterface;

interface SuccessMetadataProviderInterface
{
    /**
     * @param OrderInterface $order
     *
     * @return array
     * @api
     */
    public function getMetadataForOrderSuccess(OrderInterface $order);
}
