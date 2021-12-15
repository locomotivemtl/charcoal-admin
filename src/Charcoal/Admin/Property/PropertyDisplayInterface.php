<?php

namespace Charcoal\Admin\Property;

// From 'charcoal-admin'
use Charcoal\Admin\Property\PropertyInterface as AdminPropertyInterface;

/**
 * Defines a model property display Admin decorator.
 */
interface PropertyDisplayInterface extends AdminPropertyInterface
{
    /**
     * @param  string $displayType The display type.
     * @return self
     */
    public function setDisplayType($displayType);

    /**
     * @return string
     */
    public function displayType();

    /**
     * @param  string $displayId The display id.
     * @return self
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
}
