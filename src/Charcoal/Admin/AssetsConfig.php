<?php

namespace Charcoal\Admin;

// from charcoal-config
use Charcoal\Config\AbstractConfig;

/**
 * Assets Configuration
 */
class AssetsConfig extends AbstractConfig
{
    /**
     * @var array|mixed $collections
     */
    private $collections;

    /**
     * @return array|mixed
     */
    public function collections()
    {
        return $this->collections;
    }

    /**
     * @param array|mixed $collections Collections for AssetsConfig.
     * @return self
     */
    public function setCollections($collections)
    {
        $this->collections = $collections;

        return $this;
    }
}
