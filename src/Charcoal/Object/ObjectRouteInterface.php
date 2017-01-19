<?php

namespace Charcoal\Object;

/**
 * Defines a route to an object implementing {@see \Charcoal\Object\RoutableInterface}.
 *
 * {@see \Charcoal\Object\ObjectRoute} for a basic implementation.
 */
interface ObjectRouteInterface
{
    /**
     * Determine if the current slug is unique.
     *
     * @return boolean
     */
    public function isSlugUnique();

    /**
     * Generate a unique URL slug for routable object.
     *
     * @return ObjectRouteInterface Chainable
     */
    public function generateUniqueSlug();

    /**
     * Retrieve the object route URI.
     *
     * @return string
     */
    public function slug();

    /**
     * Retrieve the locale of the object route.
     *
     * @return string
     */
    public function lang();

    /**
     * Retrieve the foreign object type related to this route.
     *
     * @return string
     */
    public function routeObjType();

    /**
     * Retrieve the foreign object ID related to this route.
     *
     * @return string
     */
    public function routeObjId();

    /**
     * Retrieve the foreign object's template identifier.
     *
     * @return string
     */
    public function routeTemplate();
}
