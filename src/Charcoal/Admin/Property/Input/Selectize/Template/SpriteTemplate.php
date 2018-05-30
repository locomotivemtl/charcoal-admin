<?php

namespace Charcoal\Admin\Property\Input\Selectize\Template;

// local dependencies
use Charcoal\Admin\Support\BaseUrlTrait;

// from `pimple`
use Pimple\Container;

// from `charcoal-app`
use Charcoal\App\Template\AbstractTemplate;

/**
 * Controller for selectize tempalte
 * Controls the display of {@see Charcoal/Property/SpriteProperty} in the context of a selectize input
 *
 * Sprite Property Input Template
 */
class SpriteTemplate extends AbstractTemplate
{
    use BaseUrlTrait;

    /**
     * Show the sprite id besides the icon.
     *
     * @var boolean
     */
    protected $showSpriteId = true;

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setBaseUrl($container['base-url']);
    }

    /**
     * @return boolean
     */
    public function showSpriteId()
    {
        return $this->showSpriteId;
    }

    /**
     * @param boolean $flag Show the sprite id besides the icon.
     * @return self
     */
    public function setShowSpriteId($flag)
    {
        $this->showSpriteId = $flag;

        return $this;
    }
}
