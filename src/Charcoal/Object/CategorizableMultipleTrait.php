<?php

namespace Charcoal\Object;

use InvalidArgumentException;

/**
 * Defines objects that can be associated to one or more categories.
 *
 * Basic implementation of {@see \Charcoal\Object\CategorizableMultipleInterface}.
 *
 * @see \Charcoal\Object\CategoryInterface Accepted interface.
 */
trait CategorizableMultipleTrait
{
    /**
     * The type of category the object can belong to.
     *
     * @var string
     */
    private $categoryType;

    /**
     * One or more categories the object belongs to.
     *
     * @var (mixed|CategoryInterface)[]|Traversable
     */
    protected $categories;

    /**
     * Set the type of category the object can belong to.
     *
     * @param string $type The category type.
     * @throws InvalidArgumentException If the type argument is not a string.
     * @return CategorizableMultipleInterface Chainable
     */
    public function setCategoryType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Category type must be a string.'
            );
        }

        $this->categoryType = $type;

        return $this;
    }

    /**
     * Retrieve the type of category the object can belong to.
     *
     * @return string
     */
    public function getCategoryType()
    {
        return $this->categoryType;
    }

    /**
     * Set the categories the object belongs to.
     *
     * @param array|Traversable $categories The object's categories.
     * @return CategorizableMultipleInterface Chainable
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Retrieve the categories the object belongs to.
     *
     * @return array|Traversable
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
