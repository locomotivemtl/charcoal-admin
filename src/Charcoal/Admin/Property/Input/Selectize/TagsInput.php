<?php

namespace Charcoal\Admin\Property\Input\Selectize;

use \Exception;
use \InvalidArgumentException;

use Charcoal\Admin\Property\AbstractPropertyInput;
use Charcoal\Factory\FactoryInterface;
use Charcoal\Property\ObjectProperty;
use Pimple\Container;

/**
 * Tags input property
 *
 * > The Tags input is a text input in which tags can be outputted
 * -{@link http://selectize.github.io/selectize.js/}
 */
class TagsInput extends AbstractPropertyInput
{
    /**
     * The Selectize settings
     *
     * @var  array
     * - {@link http://selectize.github.io/selectize.js/}
     */
    private $selectizeOptions;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @param FactoryInterface $factory The factory, to create model objects.
     * @return ObjectProperty Chainable
     */
    public function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the model factory is not set.
     * @return FactoryInterface
     */
    private function modelFactory()
    {
        if ($this->modelFactory === null) {
            throw new Exception(
                sprintf('Model factory not set on object property "%s".')
            );
        }
        return $this->modelFactory;
    }

    /**
     * Set the selectize picker's options.
     *
     * This method overwrites existing helpers.
     *
     * @param array $opts The selectize picker options.
     * @return Tinymce Chainable
     */
    public function setSelectizeOptions(array $opts)
    {
        $this->selectizeOptions = $opts;

        return $this;
    }

    /**
     * Merge (replacing or adding) selectize picker options.
     *
     * @param array $opts The selectize picker options.
     * @return Tinymce Chainable
     */
    public function mergeSelectizeOptions(array $opts)
    {
        $this->selectizeOptions = array_merge($this->selectizeOptions, $opts);

        return $this;
    }

    /**
     * Add (or replace) an selectize picker option.
     *
     * @param string $optIdent The setting to add/replace.
     * @param mixed  $optVal   The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return self Chainable
     */
    public function addSelectizeOption($optIdent, $optVal)
    {
        if (!is_string($optIdent)) {
            throw new InvalidArgumentException(
                'Option identifier must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->selectizeOptions === null) {
            $this->selectizeOptions();
        }

        $this->selectizeOptions[$optIdent] = $optVal;

        return $this;
    }

    /**
     * Retrieve the selectize picker's options.
     *
     * @return array
     */
    public function selectizeOptions()
    {
        if ($this->selectizeOptions === null) {
            $this->setSelectizeOptions($this->defaultSelectizeOptions());
        }

        return $this->selectizeOptions;
    }

    /**
     * Retrieve the default selectize picker options.
     *
     * @return array
     */
    public function defaultSelectizeOptions()
    {
        $metadata = $this->metadata();

        $options = [];

        if (isset($metadata['data']['selectize_options'])) {
            $options = $metadata['data']['selectize_options'];
        }

        $val = $this->propertyVal();

        if ($val !== null) {
            $val = $this->p()->parseVal($val);

            if (!$this->p()->multiple()) {
                $val = [$val];
            }

            $objType = $this->p()->objType()
            foreach ($val as $v) {
                $obj = $this->modelFactory()->create($objType);
                $obj->load($v);
                if ($obj->id()) {
                    if (!isset($options['options'])) {
                        $options['options'] = [];
                    }
                    $options['options'][] = [
                        'value' => $obj->id(),
                        'text'  => (string)$obj->name(),
                        'color' => method_exists($obj, 'color') ? $obj->color() : '#4D84F1'
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * Retrieve the selectize picker's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function selectizeOptionsAsJson()
    {
        return json_encode($this->selectizeOptions());
    }
}
