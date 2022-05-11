<?php

namespace Charcoal\Admin\Property;

use Traversable;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface as ModelPropertyInterface;

/**
 * Defines a model property Admin decorator.
 */
interface PropertyInterface
{
    /**
     * @param  array|Traversable $data The object (input) data.
     * @return self
     */
    public function setData(array $data);

    /**
     * @param  string $ident The input identifier.
     * @return self
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param  ModelPropertyInterface $p The property.
     * @return self
     */
    public function setProperty(ModelPropertyInterface $p);

    /**
     * @return ModelPropertyInterface
     */
    public function getProperty();
}
