<?php

namespace Klevu\Metadata\Model;

use Klevu\Metadata\Api\Data\ProductPriceDataInterface;
use Klevu\Metadata\Traits\ArgumentValidationTrait;

class ProductPriceData implements ProductPriceDataInterface
{
    use ArgumentValidationTrait;

    /**
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $specialPrice;

    /**
     * @var string
     */
    private $currencyCode = '';

    /**
     * @return float|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->validateFloatArgument($price, __METHOD__, 'price', false);

        $this->price = $price;
    }

    /**
     * @return float|null
     */
    public function getSpecialPrice()
    {
        return $this->specialPrice;
    }

    /**
     * @param float $specialPrice
     */
    public function setSpecialPrice($specialPrice)
    {
        $this->validateFloatArgument($specialPrice, __METHOD__, 'specialPrice', false);

        $this->specialPrice = $specialPrice;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->validateStringArgument($currencyCode, __METHOD__, 'currencyCode', false);

        $this->currencyCode = $currencyCode;
    }
}
