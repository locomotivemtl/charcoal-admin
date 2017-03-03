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
     * Retrieve display value for anchor link.
     *
     * @see    \Charcoal\Admin\Property\Display\ImageDisplay::displayVal()
     * @return string
     */
    public function hrefVal()
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
