<?php

namespace Charcoal\Object;

/**
 * Categorizable defines objects that can be associated to a category.
 *
 * @see \Charcoal\Object\CategorizableMultipleInterface For objects that can to one or more categories.
 */
interface CategorizableInterface
{
    /**
     * Set the type of category the object can belong to.
     *
     * @param string $type The category type.
     * @return CategorizableInterface Chainable
     */
    public function setCategoryType($type);

    /**
     * Retrieve the type of category the object can belong to.
     *
     * @return string
     */
    public function categoryType();

    /**
     * Set the category the object belongs to.
     *
     * @param mixed $category The object's category.
     * @return CategorizableInterface Chainable
     */
    public function setCategory($category);

    /**
     * Retrieve the category the object belongs to.
     *
     * @return CategoryInterface
     */
    public function getCategory();
}
