<?php

namespace Klevu\Metadata\Provider;

use Klevu\Metadata\Api\CategoryMetadataProviderInterface;
use Klevu\Metadata\Api\ProductMetadataProviderInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

class CategoryMetadataProvider implements CategoryMetadataProviderInterface
{
    /**
     * @var ProductMetadataProviderInterface
     */
    private $productMetadataProvider;

    /**
     * CategoryMetadataProvider constructor.
     * @param ProductMetadataProviderInterface $productMetadataProvider
     */
    public function __construct(
        ProductMetadataProviderInterface $productMetadataProvider
    ) {
        $this->productMetadataProvider = $productMetadataProvider;
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
     * @return string[]
     */
    private function getCategoryNamesHierarchy(CategoryInterface $category)
    {
        if (!method_exists($category, 'getParentCategories')
            || !method_exists($category, 'getPathInStore')) {
            return [];
        }

        $parentCategories = $category->getParentCategories();
        $pathIds = array_reverse(explode(',', $category->getPathInStore()));

        $categoryNames = array_map(static function ($pathId) use ($parentCategories) {
            $parentCategory = isset($parentCategories[$pathId])
                ? $parentCategories[$pathId]
                : null;
            if (!($parentCategory instanceof CategoryInterface)) {
                return null;
            }

            switch (true) {
                case $parentCategory instanceof DataObject:
                    $return = $parentCategory->getDataUsingMethod(Category::KEY_NAME);
                    break;

                case method_exists($parentCategory, 'getName'):
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
