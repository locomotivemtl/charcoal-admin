<?php

namespace Charcoal\Admin\Property;

// From `charcoal-base`
use \Charcoal\Property\PropertyInterface as PropertyInterface;

/**
 *
 */
interface PropertyDisplayInterface
{
    /**
     * @param array $data
     * @return Display Chainable
     */
    public function setData(array $data);

    /**
     * @param string $ident
     * @throws InvalidArgumentException if the ident is not a string
     * @return PropertyDisplayInterface Chainable
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param string $displayId
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
     * @param string $display_type
     */
    public function setDisplayType($display_type);

    public function displayType();

    /**
     * @param PropertyInterface $p
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
