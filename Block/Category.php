<?php

namespace Klevu\Metadata\Block;

use Klevu\Metadata\Api\CategoryMetadataProviderInterface;
use Klevu\Metadata\Api\SerializerInterface;
use Klevu\Metadata\Service\IsEnabledDeterminer;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Category extends Template implements MetadataInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CategoryRegistryInterface
     */
    private $categoryRegistry;

    /**
     * @var CategoryMetadataProviderInterface
     */
    private $categoryMetadataProvider;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var IsEnabledDeterminer
     */
    private $isEnabledDeterminer;

    /**
     * @param TemplateContext $context
     * @param SerializerInterface $serializer
     * @param CategoryRegistryInterface $categoryRegistry
     * @param CategoryMetadataProviderInterface $categoryMetadataProvider
     * @param ProductCollectionFactory $productCollectionFactory
     * @param array $data
     * @param IsEnabledDeterminer|null $isEnabledDeterminer
     */
    public function __construct(
        TemplateContext $context,
        SerializerInterface $serializer,
        CategoryRegistryInterface $categoryRegistry,
        CategoryMetadataProviderInterface $categoryMetadataProvider,
        ProductCollectionFactory $productCollectionFactory,
        array $data = [],
        IsEnabledDeterminer $isEnabledDeterminer = null
    ) {
        parent::__construct($context, $data);

        $this->serializer = $serializer;
        $this->categoryRegistry = $categoryRegistry;
        $this->categoryMetadataProvider = $categoryMetadataProvider;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->isEnabledDeterminer = $isEnabledDeterminer ?: ObjectManager::getInstance()->get(IsEnabledDeterminer::class);
    }

    /**
     * @inheritDoc
     */
    public function getKlevuPageMeta()
    {
        $categoryMetadata = [];
        try {
            $category = $this->getCategory();

            if ($category) {
                $productCollection = $this->getProductCollection();
                if (!$productCollection) {
                    // Ensure no records are returned if a product collection isn't already available
                    $productCollection = $this->productCollectionFactory->create();
                    $productCollection->addAttributeToFilter('entity_id', -1);
                }

                $categoryMetadata = $this->categoryMetadataProvider->getMetadataForCategory(
                    $category,
                    $productCollection
                );
            }
        } catch (\Exception $e) {
            $this->_logger->error(
                sprintf("Unable to retrieve Category Metadata: %s", $e->getMessage()),
                ['originalException' => $e]
            );
        }

        return $this->serializer->serialize($categoryMetadata);
    }

    /**
     * @return CategoryInterface|null
     */
    public function getCategory()
    {
        return $this->categoryRegistry->getCurrentCategory();
    }

    /**
     * @return BlockInterface|null
     */
    public function getListProductsBlock()
    {
        $listProductsBlockName = trim((string)$this->getData('list_products_block_name'));
        if (!$listProductsBlockName) {
            return null;
        }

        $layout = null;
        try {
            $layout = $this->getLayout();
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage(), ['originalException' => $e]);
        }
        if (!$layout) {
            return null;
        }

        return $layout->getBlock($listProductsBlockName) ?: null;
    }

    /**
     * @return ProductCollection|null
     */
    public function getProductCollection()
    {
        $listProductsBlock = $this->getListProductsBlock();

        return $listProductsBlock && method_exists($listProductsBlock, 'getLoadedProductCollection')
            ? $listProductsBlock->getLoadedProductCollection()
            : null;
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
