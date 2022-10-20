<?php

namespace Klevu\Metadata\Provider;

use Klevu\Metadata\Api\CategoryMetadataProviderInterface;
use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Klevu\Metadata\Constants;

class CategoryMetadataProvider implements CategoryMetadataProviderInterface
{
    /**
     * @var ProductMetadataProviderInterface
     */
    private $productMetadataProvider;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @param ProductMetadataProviderInterface $productMetadataProvider
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        ProductMetadataProviderInterface $productMetadataProvider,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->productMetadataProvider = $productMetadataProvider;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @param CategoryInterface $category
     * @param ProductCollection|null $productCollectionOverride
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMetadataForCategory(
        CategoryInterface $category,
        ProductCollection $productCollectionOverride = null
    ) {
        return [
            'platform' => Constants::KLEVU_PLATFORM_TYPE,
            'pageType' => static::PAGE_TYPE,
            'categoryName' => implode(';', $this->getCategoryNamesHierarchy($category)),
            'categoryUrl' => $category->getUrl(),
            'categoryProducts' => $this->getCategoryProductsMetadata(
                $category,
                $productCollectionOverride
            ),
        ];
    }

    /**
     * @param CategoryInterface $category
     * @return array|string[]
     * @throws LocalizedException
     */
    private function getCategoryNamesHierarchy(CategoryInterface $category)
    {
        if (!method_exists($category, 'getPathInStore')) {
            return [];
        }

        $pathIds = array_reverse(explode(',', $category->getPathInStore()));
        $parentCategories = $this->categoryCollectionFactory->create();
        $parentCategories->addFieldToFilter('entity_id', ['in' => $pathIds]);
        $parentCategories->addAttributeToSelect('name');

        $categoryNames = array_map(static function ($pathId) use ($parentCategories) {
            $parentCategory = $parentCategories->getItemById($pathId);
            switch (true) {
                case $parentCategory instanceof DataObject:
                    $return = $parentCategory->getDataUsingMethod(Category::KEY_NAME);
                    break;

                case $parentCategory && method_exists($parentCategory, 'getName'):
                    $return = $parentCategory->getName();
                    break;

                default:
                    $return = '';
                    break;
            }

            return (string)$return;
        }, $pathIds);

        return array_filter($categoryNames, static function ($categoryName) {
            return null !== $categoryName;
        });
    }

    /**
     * @param CategoryInterface $category
     * @param ProductCollection|null $productCollection
     * @return array
     * @throws NoSuchEntityException
     */
    private function getCategoryProductsMetadata(
        CategoryInterface $category,
        ProductCollection $productCollection = null
    ) {
        if (null === $productCollection && method_exists($category, 'getProductCollection')) {
            $productCollection = $category->getProductCollection();
        }

        if (!$productCollection) {
            return [];
        }

        $categoryProductsMetadata = [];
        foreach ($productCollection->getItems() as $product) {
            if (!($product instanceof ProductInterface)) {
                continue;
            }

            $categoryProductsMetadata[] = array_intersect_key(
                $this->productMetadataProvider->getMetadataForProduct($product),
                [
                    'itemId' => '',
                    'itemGroupId' => '',
                ]
            );
        }

        return $categoryProductsMetadata;
    }
}
