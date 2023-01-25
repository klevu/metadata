<?php

namespace Klevu\Metadata\Test\Integration\Service\Convertor;

use Klevu\Metadata\Service\Convertor\OrderSyncItemDataConvertor;
use Klevu\Search\Provider\Sync\Order\Item\Type\DefaultDataProvider;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class OrderSyncItemDataConvertorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @dataProvider missingRequiredFieldsDataProvider
     */
    public function testThrowsExceptionIfRequiredDataMissing($field)
    {
        $this->setUpPhp5();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Required Order Item field %s missing from analytics data', $field)
        );

        $orderItem = [
            DefaultDataProvider::ORDER_ID => '1',
            DefaultDataProvider::ORDER_ITEM_ID => '2',
            DefaultDataProvider::TYPE => DefaultDataProvider::DATA_TYPE,
            DefaultDataProvider::PRODUCT_NAME => 'Test Product',
            DefaultDataProvider::PRODUCT_ID => '4-3',
            DefaultDataProvider::PRODUCT_GROUP_ID => '4',
            DefaultDataProvider::PRODUCT_VARIANT_ID => '3',
            DefaultDataProvider::UNIT => '1.000',
            DefaultDataProvider::UNIT_PRICE => '1234.56',
            DefaultDataProvider::CURRENCY => 'USD'
        ];
        unset($orderItem[$field]);

        $convertor = $this->instantiateConvertor();
        $actual = $convertor->convert($orderItem);
        $expected = [];

        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider missingOptionalFieldsDataProvider
     */
    public function testReturnsDataIfOptionalDataMissing($optionalField)
    {
        $this->setUpPhp5();

        $keys = array_keys($optionalField);
        $optionalFieldKey = $keys[0];
        $field = $optionalField[$optionalFieldKey];

        $orderItem = [
            DefaultDataProvider::ORDER_ID => '1',
            DefaultDataProvider::ORDER_ITEM_ID => '2',
            DefaultDataProvider::TYPE => DefaultDataProvider::DATA_TYPE,
            DefaultDataProvider::PRODUCT_NAME => 'Test Product',
            DefaultDataProvider::PRODUCT_ID => '4-3',
            DefaultDataProvider::PRODUCT_GROUP_ID => '4',
            DefaultDataProvider::PRODUCT_VARIANT_ID => '3',
            DefaultDataProvider::UNIT => '1.000',
            DefaultDataProvider::UNIT_PRICE => 1234.56,
            DefaultDataProvider::CURRENCY => 'USD'
        ];

        $expected = [
            'order_id' => $orderItem[DefaultDataProvider::ORDER_ID],
            'klevu_type' => $orderItem[DefaultDataProvider::TYPE],
            'item_id' => $orderItem[DefaultDataProvider::PRODUCT_ID],
            'item_group_id' => $orderItem[DefaultDataProvider::PRODUCT_GROUP_ID],
            'item_variant_id' => $orderItem[DefaultDataProvider::PRODUCT_VARIANT_ID],
            'unit_price' => $orderItem[DefaultDataProvider::UNIT_PRICE],
            'currency' => $orderItem[DefaultDataProvider::CURRENCY],
            'item_name' => $orderItem[DefaultDataProvider::PRODUCT_NAME],
            'order_line_id' => $orderItem[DefaultDataProvider::ORDER_ITEM_ID],
            'units' => (float)$orderItem[DefaultDataProvider::UNIT],
        ];

        unset($orderItem[$field]);

        $expected = array_filter($expected, static function ($key) use ($optionalFieldKey) {
            return $optionalFieldKey !== $key;
        }, ARRAY_FILTER_USE_KEY);

        $convertor = $this->instantiateConvertor();
        $actual = $convertor->convert($orderItem);

        $this->assertSame($expected, $actual);
    }

    public function testReturnsData()
    {
        $this->setUpPhp5();

        $orderItem = [
            DefaultDataProvider::ORDER_ID => '1',
            DefaultDataProvider::ORDER_ITEM_ID => '2',
            DefaultDataProvider::TYPE => DefaultDataProvider::DATA_TYPE,
            DefaultDataProvider::PRODUCT_NAME => 'Test Product',
            DefaultDataProvider::PRODUCT_ID => '4-3',
            DefaultDataProvider::PRODUCT_GROUP_ID => '4',
            DefaultDataProvider::PRODUCT_VARIANT_ID => '3',
            DefaultDataProvider::UNIT => '1.000',
            DefaultDataProvider::UNIT_PRICE => '1234.56',
            DefaultDataProvider::CURRENCY => 'USD'
        ];

        $convertor = $this->instantiateConvertor();
        $actual = $convertor->convert($orderItem);
        $expected = [
            'order_id' => $orderItem[DefaultDataProvider::ORDER_ID],
            'klevu_type' => $orderItem[DefaultDataProvider::TYPE],
            'item_id' => $orderItem[DefaultDataProvider::PRODUCT_ID],
            'item_group_id' => $orderItem[DefaultDataProvider::PRODUCT_GROUP_ID],
            'item_variant_id' => $orderItem[DefaultDataProvider::PRODUCT_VARIANT_ID],
            'unit_price' => $orderItem[DefaultDataProvider::UNIT_PRICE],
            'currency' => $orderItem[DefaultDataProvider::CURRENCY],
            'item_name' => $orderItem[DefaultDataProvider::PRODUCT_NAME],
            'order_line_id' => $orderItem[DefaultDataProvider::ORDER_ITEM_ID],
            'units' => (float)$orderItem[DefaultDataProvider::UNIT],
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    public function missingRequiredFieldsDataProvider()
    {
        return [
            [DefaultDataProvider::ORDER_ID],
            [DefaultDataProvider::TYPE],
            [DefaultDataProvider::PRODUCT_ID],
            [DefaultDataProvider::PRODUCT_GROUP_ID],
            [DefaultDataProvider::PRODUCT_VARIANT_ID],
            [DefaultDataProvider::UNIT_PRICE],
            [DefaultDataProvider::CURRENCY],
        ];
    }

    /**
     * @return array[]
     */
    public function missingOptionalFieldsDataProvider()
    {
        return [
            [['item_name' => DefaultDataProvider::PRODUCT_NAME]],
            [['order_line_id' => DefaultDataProvider::ORDER_ITEM_ID]],
            [['units' => DefaultDataProvider::UNIT]],
        ];
    }

    /**
     * @return void
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setUpPhp5()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @return OrderSyncItemDataConvertor|mixed
     */
    private function instantiateConvertor()
    {
        return $this->objectManager->get(OrderSyncItemDataConvertor::class);
    }
}
