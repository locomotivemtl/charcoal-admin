<?php

namespace Charcoal\Object;

/**
 * Defines objects that can be associated to one or more categories.
 *
 * @see \Charcoal\Object\CategorizableInterface For objects that can only belong to a single category.
 */
interface CategorizableMultipleInterface
{
    /**
     * Set the type of category the object can belong to.
     *
     * @param string $type The category type.
     * @return CategorizableMultipleInterface Chainable
     */
    public function setCategoryType($type);

    /**
     * Retrieve the type of category the object can belong to.
     *
     * @return string
     */
    public function categoryType();

    /**
     * Set the categories the object belongs to.
     *
     * @param array|Traversable $categories The object's categories.
     * @return CategorizableMultipleInterface Chainable
     */
    public function setCategories($categories);

    /**
     * Retrieve the categories the object belongs to.
     *
     * @return array|Traversable
     */
    public function categories();
}
