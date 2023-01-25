<?php

namespace Klevu\Metadata\Provider;

use Klevu\Metadata\Api\SuccessMetadataProviderInterface;
use Klevu\Metadata\Constants;
use Klevu\Search\Api\Provider\Sync\Order\Item\DataProviderInterface as OrderItemDataProviderInterface;
use Klevu\Search\Api\Provider\Sync\Order\ItemsToSyncProviderInterface;
use Klevu\Search\Api\Service\Convertor\Sync\Order\ItemDataConvertorInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProduct;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class SuccessMetadataProvider implements SuccessMetadataProviderInterface
{
    const PAGE_TYPE = 'checkout';

    /**
     * @var ItemsToSyncProviderInterface
     */
    private $itemsToSyncProvider;
    /**
     * @var OrderItemDataProviderInterface
     */
    private $dataProvider;
    /**
     * @var ItemDataConvertorInterface
     */
    private $orderSyncItemDataConvertor;

    /**
     * @param ItemsToSyncProviderInterface $itemsToSyncProvider
     * @param OrderItemDataProviderInterface $dataProvider
     * @param ItemDataConvertorInterface $orderSyncItemDataConvertor
     */
    public function __construct(
        ItemsToSyncProviderInterface $itemsToSyncProvider,
        OrderItemDataProviderInterface $dataProvider,
        ItemDataConvertorInterface $orderSyncItemDataConvertor
    ) {
        $this->itemsToSyncProvider = $itemsToSyncProvider;
        $this->dataProvider = $dataProvider;
        $this->orderSyncItemDataConvertor = $orderSyncItemDataConvertor;
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     * @api
     */
    public function getMetadataForOrderSuccess(OrderInterface $order)
    {
        $orderItems = $this->getMetadataForOrderItems($order);

        return [
            'platform' => Constants::KLEVU_PLATFORM_TYPE,
            'pageType' => static::PAGE_TYPE,
            'orderItems' => array_values($orderItems),
        ];
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     */
    private function getMetadataForOrderItems(OrderInterface $order)
    {
        $orderItems = $this->itemsToSyncProvider->getItems(null, (int)$order->getId());
        if (!$orderItems) {
            $orderItems = [];
            foreach ($this->getItemsToSync($order) as $orderItem) {
                $orderItems[$orderItem->getId()] = $orderItem;
            }
        }

        return array_filter(
            array_map(
                [$this, 'getMetadataForOrderItem'],
                $orderItems
            )
        );
    }

    /**
     * @param OrderItemInterface $orderItem
     *
     * @return string[]
     */
    private function getMetadataForOrderItem(OrderItemInterface $orderItem)
    {
        $item = $this->dataProvider->getData($orderItem);

        return $this->orderSyncItemDataConvertor->convert($item);
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     */
    private function getItemsToSync(OrderInterface $order)
    {
        $items = method_exists($order, 'getAllVisibleItems')
            ? $order->getAllVisibleItems()
            : [];

        return $this->removeDuplicateGroupedProducts($items);
    }

    /**
     * @param array $items
     *
     * @return array
     */
    private function removeDuplicateGroupedProducts(array $items)
    {
        $uniqueGroupedProductIds = [];
        foreach ($items as $key => $item) {
            /** @var OrderItemInterface $item */
            if ($item->getProductType() !== GroupedProduct::TYPE_CODE) {
                continue;
            }
            if ($item->getId() === null) {
                unset($items[$key]);
                continue;
            }
            $groupProductData = $item->getProductOptions();
            if (empty($groupProductData["super_product_config"]["product_id"])) {
                unset($items[$key]);
                continue;
            }
            $groupedProductId = $groupProductData["super_product_config"]["product_id"];
            if (in_array((int)$groupedProductId, $uniqueGroupedProductIds, true)) {
                unset($items[$key]);
                continue;
            }
            $uniqueGroupedProductIds[] = (int)$groupedProductId;
        }

        return $items;
    }
}
