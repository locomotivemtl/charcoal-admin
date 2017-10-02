<?php

namespace Charcoal\Admin\Action\Selectize;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\ModelInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\SaveAction as BaseSaveAction;
use Charcoal\Admin\Service\SelectizeRenderer;

/**
 * Selectize Save Action
 */
class SaveAction extends BaseSaveAction
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
