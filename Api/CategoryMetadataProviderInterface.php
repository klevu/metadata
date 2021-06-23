<?php

namespace Klevu\Metadata\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Exception\NoSuchEntityException;

interface CategoryMetadataProviderInterface
{
    const PAGE_TYPE = 'category';

    /**
     * @api
     * @param CategoryInterface $category
     * @param ProductCollection|null $productCollectionOverride
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMetadataForCategory(
        CategoryInterface $category,
        ProductCollection $productCollectionOverride = null
    );
}
