<?php

namespace Charcoal\Admin\Property\Display;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\ImageAttributesTrait;
use Charcoal\Admin\Property\AbstractPropertyDisplay;

/**
 * Image Display Property
 */
class ImageDisplay extends AbstractPropertyDisplay
{
    use ImageAttributesTrait;

    /**
     * The base URI for the Charcoal application.
     *
     * @var \Psr\Http\Message\UriInterface|string
     */
    public $baseUrl;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->baseUrl = $container['base-url'];
    }

    /**
     * Retrieve display value.
     *
     * @see    \Charcoal\Admin\Property\Display\LinkDisplay::hrefVal()
     * @return string
     */
    public function displayVal()
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
}
