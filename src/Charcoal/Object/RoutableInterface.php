<?php

namespace Charcoal\Object;

/**
 * Defines an object as routable.
 *
 * Routable objects are accessible via a URI. The interface provides a "slug" property
 * to track the latest "pretty" URI path.
 *
 * Available implementation as trait:
 * - {@see \Charcoal\Object\RoutableTrait}.
 */
interface RoutableInterface
{
    /**
     * Set the object's URL slug pattern.
     *
     * @param  mixed $pattern The slug pattern.
     * @return RoutableInterface Chainable
     */
    public function setSlugPattern($pattern);

    /**
     * Retrieve the object's URL slug pattern.
     *
     * @return string|null
     */
    public function slugPattern();

    /**
     * Set the object's URL slug.
     *
     * @param  mixed $slug The slug.
     * @return RoutableInterface Chainable
     */
    public function setSlug($slug);

    /**
     * Retrieve the object's URL slug.
     *
     * @return string|null
     */
    public function slug();

    /**
     * Generate a URL slug from the object's URL slug pattern.
     *
     * @return string|null
     */
    public function generateSlug();

    /**
     * Retrieve the object's URI.
     *
     * @return string|null
     */
    public function url();

    /**
     * Create a route object.
     *
     * @return \Charcoal\Object\ObjectRouteInterface
     */
    public function createRouteObject();

    /**
     * Retrieve the class name of the object route model.
     *
     * @return string
     */
    public function objectRouteClass();
}
