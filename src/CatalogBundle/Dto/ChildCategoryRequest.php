<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Model\Category;

/**
 * Class ChildCategoryRequest
 *
 * @package FourPaws\CatalogBundle\Dto
 */
class ChildCategoryRequest extends AbstractCatalogRequest implements CatalogCategorySearchRequestInterface
{
    /**
     * @var Category
     */
    protected $category;
    /**
     * @var CategoryCollection
     */
    protected $landingCollection;

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return CatalogCategorySearchRequestInterface
     */
    public function setCategory(Category $category): CatalogCategorySearchRequestInterface
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return CategoryCollection
     */
    public function getLandingCollection(): CategoryCollection
    {
        return $this->landingCollection;
    }

    /**
     * @param CategoryCollection $landingCollection
     *
     * @return CatalogCategorySearchRequestInterface
     */
    public function setLandingCollection(CategoryCollection $landingCollection): CatalogCategorySearchRequestInterface
    {
        $this->landingCollection = $landingCollection;

        return $this;
    }
}
