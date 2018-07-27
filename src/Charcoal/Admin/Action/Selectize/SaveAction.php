<?php

namespace Charcoal\Admin\Action\Selectize;

// From Pimple
use Pimple\Container;

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
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'selectize_obj_type', 'selectize_prop_ident', 'selectize_property'
        ], parent::validDataFromRequest());
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

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setSelectizeRenderer($container['selectize/renderer']);
        $this->setPropertyInputFactory($container['property/input/factory']);
        $this->setPropertyFactory($container['property/factory']);
    }
}
