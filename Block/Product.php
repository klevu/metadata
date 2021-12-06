<?php

namespace Klevu\Metadata\Block;

use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Klevu\Metadata\Api\SerializerInterface;
use Klevu\Metadata\Service\IsEnabledDeterminer;
use Klevu\Registry\Api\ProductRegistryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Product extends Template implements MetadataInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ProductRegistryInterface
     */
    private $productRegistry;

    /**
     * @var ProductMetadataProviderInterface
     */
    private $productMetadataProvider;

    /**
     * @var IsEnabledDeterminer
     */
    private $isEnabledDeterminer;

    /**
     * @param TemplateContext $context
     * @param SerializerInterface $serializer
     * @param ProductRegistryInterface $productRegistry
     * @param ProductMetadataProviderInterface $productMetadataProvider
     * @param array $data
     * @param IsEnabledDeterminer|null $isEnabledDeterminer
     */
    public function __construct(
        TemplateContext $context,
        SerializerInterface $serializer,
        ProductRegistryInterface $productRegistry,
        ProductMetadataProviderInterface $productMetadataProvider,
        array $data = [],
        IsEnabledDeterminer $isEnabledDeterminer = null
    ) {
        parent::__construct($context, $data);

        $this->serializer = $serializer;
        $this->productRegistry = $productRegistry;
        $this->productMetadataProvider = $productMetadataProvider;
        $this->isEnabledDeterminer = $isEnabledDeterminer ?: ObjectManager::getInstance()->get(IsEnabledDeterminer::class);
    }

    /**
     * @return string
     */
    public function getKlevuPageMeta()
    {
        if (!$this->getData('klevu_page_meta')) {
            $klevuPageMeta = [];
            try {
                $product = $this->getProduct();

                if ($product) {
                    $klevuPageMeta = $this->productMetadataProvider->getMetadataForProduct($product);
                }
            } catch (\Exception $e) {
                $this->_logger->error(
                    sprintf("Unable to retrieve Product Metadata: %s", $e->getMessage()),
                    ['originalException' => $e]
                );
            }

            $this->setData(
                'klevu_page_meta',
                $this->serializer->serialize($klevuPageMeta)
            );
        }

        return $this->getData('klevu_page_meta');
    }

    /**
     * @return ProductInterface|null
     */
    public function getProduct()
    {
        return $this->productRegistry->getCurrentProduct();
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    protected function _toHtml()
    {
        try {
            $store = $this->_storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage(), ['originalException' => $e]);

            return '';
        }

        /** @noinspection PhpCastIsUnnecessaryInspection */
        if (!$this->isEnabledDeterminer->execute((int)$store->getId())) {
            return '';
        }

        return parent::_toHtml();
    }
}
