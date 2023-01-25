<?php

namespace Klevu\Metadata\Service\Convertor;

use InvalidArgumentException;
use Klevu\Search\Api\Service\Convertor\Sync\Order\ItemDataConvertorInterface;
use Klevu\Search\Provider\Sync\Order\Item\Type\DefaultDataProvider;

class OrderSyncItemDataConvertor implements ItemDataConvertorInterface
{
    /**
     * @var array
     */
    private $requiredFields = [
        'order_id' => DefaultDataProvider::ORDER_ID,
        'klevu_type' => DefaultDataProvider::TYPE,
        'item_id' => DefaultDataProvider::PRODUCT_ID,
        'item_group_id' => DefaultDataProvider::PRODUCT_GROUP_ID,
        'item_variant_id' => DefaultDataProvider::PRODUCT_VARIANT_ID,
        'unit_price' => DefaultDataProvider::UNIT_PRICE,
        'currency' => DefaultDataProvider::CURRENCY,
    ];
    /**
     * @var array
     */
    private $optionsFields = [
        'item_name' => DefaultDataProvider::PRODUCT_NAME,
        'order_line_id' => DefaultDataProvider::ORDER_ITEM_ID,
        'units' => DefaultDataProvider::UNIT,
    ];
    /**
     * @var string[]
     */
    private $castToType = [
        'units' => 'float'
    ];

    /**
     * @param array $orderItem
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function convert(array $orderItem)
    {
        $this->validate($orderItem);

        $return = [];
        foreach ($this->requiredFields as $key => $requiredField) {
            $return[$key] = $orderItem[$requiredField];
        }
        foreach ($this->optionsFields as $key => $optionsField) {
            if (array_key_exists($optionsField, $orderItem)) {
                $return[$key] = $orderItem[$optionsField];
            }
        }
        foreach ($this->castToType as $key => $type) {
            if (array_key_exists($key, $return)) {
                $return[$key] = $this->castToType($return[$key], $type);
            }
        }

        return $return;
    }

    /**
     * @param array $orderItem
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(array $orderItem)
    {
        foreach ($this->requiredFields as $requiredField) {
            if (!isset($orderItem[$requiredField])) {
                throw new InvalidArgumentException(
                    __('Required Order Item field %1 missing from analytics data', $requiredField)
                );
            }
        }
    }

    /**
     * @param string $value
     * @param string $type
     *
     * @return float|string
     */
    private function castToType($value, $type)
    {
        switch ($type) {
            case 'float':
                $return = (float)$value;
                break;
            default:
                $return = $value;
                break;
        }

        return $return;
    }
}
