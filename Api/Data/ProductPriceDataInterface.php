<?php

namespace Klevu\Metadata\Api\Data;

interface ProductPriceDataInterface
{
    /**
     * @api
     * @return float|null
     */
    public function getPrice();

    /**
     * @api
     * @param float $price
     * @return void
     */
    public function setPrice($price);

    /**
     * @api
     * @return float|null
     */
    public function getSpecialPrice();

    /**
     * @api
     * @param float $specialPrice
     * @return void
     */
    public function setSpecialPrice($specialPrice);

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @param string $currencyCode
     * @return void
     */
    public function setCurrencyCode($currencyCode);
}
