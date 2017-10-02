<?php

namespace Charcoal\Admin\Action\Selectize;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\UpdateAction as BaseUpdateAction;
use Charcoal\Admin\Service\SelectizeRenderer;

/**
 * Selectize Update Action
 */
class UpdateAction extends BaseUpdateAction
{
    use SelectizeRendererAwareTrait;

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setSelectizeRenderer($container['selectize/renderer']);
        $this->setPropertyInputFactory($container['property/input/factory']);
    }

    /**
     * @return array
     */
    public function results()
    {
        $results = parent::results();

        if ($this->success() === true) {
            $results['selectize'] = $this->selectizeVal($this->obj()->id());
        }

        return $results;
    }
}
