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

        if ($val && !parse_url($val, PHP_URL_SCHEME)) {
            if (!in_array($val[0], [ '/', '#', '?' ])) {
                return $this->baseUrl->withPath($val);
            }
        }

        return $val;
    }
}
