<?php

namespace Charcoal\Admin\Property;

use Traversable;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

/**
 * Defines a property display element.
 */
interface PropertyDisplayInterface
{
    /**
     * @param array|Traversable $data The display data.
     * @return Display Chainable
     */
    public function setData(array $data);

    /**
     * @param string $ident The display identifier.
     * @return PropertyDisplayInterface Chainable
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param string $displayId The display id.
     * @return Display Chainable
     */
    public function setDisplayId($displayId);

    /**
     * @return string
     */
    public function displayId();

    /**
     * @return string
     */
    public function displayName();

    /**
     * @return string
     */
    public function displayVal();

    /**
     * @param string $displayType The display type.
     * @return PropertyDisplayInterface Chainable
     */
    public function setDisplayType($displayType);

    /**
     * @return string
     */
    public function displayType();

    /**
     * @param PropertyInterface $p The property.
     * @return PropertyDisplayInterface Chainable
     */
    public function setProperty(PropertyInterface $p);

    /**
     * @return PropertyInterface
     */
    public function property();

    /**
     * @return PropertyInterface
     */
    public function p();
}
