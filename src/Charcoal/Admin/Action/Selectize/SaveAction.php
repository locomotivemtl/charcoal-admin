<?php

namespace Charcoal\Admin\Action\Selectize;

use Charcoal\Admin\Action\Object\SaveAction as DefaultSaveAction;
use Charcoal\Admin\Service\SelectizeRenderer;
use Charcoal\Model\ModelInterface;
use Pimple\Container;

/**
 * Selectize Save Action
 */
class SaveAction extends DefaultSaveAction
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
