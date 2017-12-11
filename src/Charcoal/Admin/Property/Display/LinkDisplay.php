<?php

namespace Charcoal\Admin\Property\Display;

use Charcoal\Admin\Property\AbstractPropertyDisplay;
use Pimple\Container;

/**
 * Link Display Property
 */
class LinkDisplay extends AbstractPropertyDisplay
{
    /**
     * The base URI for the Charcoal application.
     *
     * @var string|\Psr\Http\Message\UriInterface
     */
    public $baseUrl;

    /**
     * Retrieve display value for anchor link.
     *
     * @see    \Charcoal\Admin\Property\Display\ImageDisplay::displayVal()
     * @return string
     */
    public function hrefVal()
    {
        $val = parent::displayVal();
        if (empty($val)) {
            return '';
        }

        $parts = parse_url($val);
        if (empty($parts['scheme']) && !in_array($val[0], [ '/', '#', '?' ])) {
            $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
            $query = isset($parts['query']) ? $parts['query'] : '';
            $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';
            $val   = $this->baseUrl->withPath($path)->withQuery($query)->withFragment($hash);
        }

        return $val;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->baseUrl = $container['base-url'];
    }
}
