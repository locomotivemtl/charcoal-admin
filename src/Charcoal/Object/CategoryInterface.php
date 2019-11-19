<?php

namespace Charcoal\Object;

/**
 *
 */
interface CategoryInterface
{
    /**
     * @param string $type The category item type.
     * @return CategoryInterface Chainable
     */
    public function setCategoryItemType($type);

    /**
     * @return string
     */
    public function getCategoryItemType();

    /**
     * Get the number of items in this category.
     * @return array
     */
    public function numCategoryItems();

    /**
     * @return boolean
     */
    public function hasCategoryItems();

    /**
     * @return array
     */
    public function getCategoryItems();
}
