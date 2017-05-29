<?php

namespace Charcoal\Admin\Action\Selectize;

use Charcoal\Admin\Action\Object\LoadAction as DefaultLoadAction;
use Charcoal\Admin\Service\SelectizeRenderer;
use Pimple\Container;

/**
 * Selectize Load Action
 */
class LoadAction extends DefaultLoadAction
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
     * Sets the entity data, from associative array map (or any other Traversable).
     *
     * This function takes an array and fill the property with its value.
     *
     * @param array $data The entity data. Will call setters.
     * @return self Chainable
     * @see self::offsetSet()
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['selectize_input_ident'])) {
            error_log(var_export($data['selectize_prop_ident'], true));
        }

        return $this;
    }

    /**
     * Fetch ids from Object Collection.
     *
     * @return array
     */
    private function parseCollectionIds()
    {
        $collection = $this->objCollection();
        $ids = [];

        foreach ($collection as $object) {
            $ids[] = $object->id();
        }

        return $ids;
    }

    /**
     * @return array
     */
    public function results()
    {
        $results = parent::results();

        if ($this->success() === true) {
            $results['selectize'] = $this->selectizeVal($this->parseCollectionIds());
        }

        return $results;
    }
}
