<?php

namespace Klevu\Metadata\Provider;

use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class ProductMetadataProvider implements ProductMetadataProviderInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductMetadataProviderInterface[]
     */
    private $productTypeMetadataProviders = [];

    /**
     * ProductMetadataProvider constructor.
     * @param LoggerInterface $logger
     * @param array|null $productTypeMetadataProviders
     */
    public function __construct(
        LoggerInterface $logger,
        array $productTypeMetadataProviders = null
    ) {
        $this->logger = $logger;

        if (null !== $productTypeMetadataProviders) {
            array_walk(
                $productTypeMetadataProviders,
                function (ProductMetadataProviderInterface $productMetadataProvider, $productType) {
                    $this->productTypeMetadataProviders[$productType] = $productMetadataProvider;
                }
            );
        }
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getMetadataForProduct(ProductInterface $product)
    {
        $typeId = $product->getTypeId();

        if (!isset($this->productTypeMetadataProviders[$typeId])) {
            $this->logger->error(sprintf(
                'No productTypeMetadataProvider set for typeId "%s"',
                $typeId
            ), [
                'registeredProductTypeMetadataProviders' => array_keys($this->productTypeMetadataProviders),
            ]);

            return [];
        }

        $return = null;
        try {
            $return = $this->productTypeMetadataProviders[$typeId]->getMetadataForProduct($product);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage(), ['originalException' => $e]);
        }

        return $return;
    }
}
