<?php

namespace Charcoal\Admin\Property\Display;

// From Pimple
use Pimple\Container;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;
use Charcoal\Property\ImageProperty;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyDisplay;
use Charcoal\Admin\Support\BaseUrlTrait;

/**
 * Link Display Property
 */
class LinkDisplay extends AbstractPropertyDisplay
{
    use BaseUrlTrait;

    /**
     * @return string
     */
    public function displayVal()
    {
        $prop = $this->p();
        $value = (array)$this->propertyVal();
        $links = [];

        foreach ($value as $val) {
            if (empty($val)) {
                continue;
            }

            $links[] = sprintf(
                '<a href="%s">%s</a>',
                $this->getLocalUrl($val),
                $val
            );
        }

        return $prop->displayVal($links);
    }

    /**
     * @return string[]|\Generator
     */
    public function displayValList()
    {
        $prop = $this->p();
        $value = (array)$this->propertyVal();

        foreach ($value as $val) {
            if (empty($val)) {
                continue;
            }

            $link = sprintf(
                '<a href="%s">%s</a>',
                $this->getLocalUrl($val),
                basename($val)
            );

            yield $prop->displayVal($link);
        }
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string $path The file path.
     * @return string
     */
    protected function getLocalUrl($path)
    {
        $prop = $this->property();
        if ($prop instanceof FileProperty) {
            if ($prop->publicAccess() === false) {
                $query = http_build_query([
                    'disk' => $prop->filesystem(),
                    'path' => $path,
                ]);
                return $this->adminUrl('filesystem/download')->withQuery($query);
            }
        }

        return $this->baseUrl($path);
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

        // Satisfies BaseUrlTrait dependencies
        $this->setBaseUrl($container['base-url']);
        $this->setAdminUrl($container['admin/base-url']);
    }
}
