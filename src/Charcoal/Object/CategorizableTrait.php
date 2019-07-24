<?php

namespace Charcoal\Object;

use InvalidArgumentException;

/**
 * Categorizable defines objects that can be associated to a category.
 *
 * Basic implementation of {@see \Charcoal\Object\CategorizableInterface}.
 *
 * @see \Charcoal\Object\CategoryInterface Accepted interface.
 * @see \Charcoal\Object\CategorizableMultipleTrait For objects that can to one or more categories.
 */
trait CategorizableTrait
{
    /**
     * The type of category the object can belong to.
     *
     * @var string $categoryType
     */
    private $categoryType;

    /**
     * The category the object belongs to.
     *
     * @var mixed|CategoryInterface $category
     */
    protected $category;

    /**
     * Set the type of category the object can belong to.
     *
     * @param string $type The category type.
     * @throws InvalidArgumentException If the type argument is not a string.
     * @return CategorizableInterface Chainable
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
    public function categoryType()
    {
        return $this->categoryType;
    }

    /**
     * Set the category the object belongs to.
     *
     * @param mixed $category The object's category.
     * @return CategorizableInterface Chainable
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Retrieve the category the object belongs to.
     *
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }
}
