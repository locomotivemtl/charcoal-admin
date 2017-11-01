<?php

namespace Charcoal\Admin;

// Module `pimple/pimple` dependencies
use Pimple\Container;

// Module `charcoal-factory` dependencies
use Charcoal\Factory\FactoryInterface;

// Module `charcoal-app` dependencies
use Charcoal\App\Script\AbstractScript;

// Module `charcoal-property` dependencies
use Charcoal\Property\PropertyInterface;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

/**
 *
 */
abstract class AdminScript extends AbstractScript
{
    use TranslatorAwareTrait;

    /**
     * The base URI.
     *
     * @var UriInterface
     */
    protected $baseUrl;
    
    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->setModelFactory($container['model/factory']);
        $this->setTranslator($container['translator']);

        $this->setBaseUrl($container['base-url']);

        parent::setDependencies($container);
    }


    /**
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory()
    {
        return $this->modelFactory;
    }

    /**
     * Retrieve the base URI of the application.
     *
     * @return UriInterface|string
     */
    protected function baseUrl()
    {
        return rtrim($this->baseUrl, '/').'/';
    }

    /**
     * @param PropertyInterface $prop  The property to retrieve input from.
     * @param string            $label The input label.
     * @return mixed
     */
    protected function propertyToInput(PropertyInterface $prop, $label = null)
    {
        $climate = $this->climate();

        if ($label === null) {
            $label = sprintf(
                'Enter value for "%s":',
                $prop->label()
            );
        }

        if ($prop->type() == 'password') {
            return $this->passwordInput($prop, $label);
        } elseif ($prop->type() == 'boolean') {
            return $this->booleanInput($prop, $label);
        } else {
            $input = $climate->input($label);
            if ($prop->type() == 'text' || $prop->type == 'html') {
                $input->multiLine();
            }
        }
        return $input;
    }

    /**
     * Get a CLI input from a boolean property.
     *
     * @param PropertyInterface $prop  The property to retrieve input from.
     * @param string            $label The input label.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function booleanInput(PropertyInterface $prop, $label)
    {
        $climate = $this->climate();

        $opts = [
            1 => $prop->trueLabel(),
            0 => $prop->falseLabel()
        ];
        $input = $climate->radio(
            $label,
            $opts
        );
        return $input;
    }

    /**
     * Get a CLI password input (hidden) from a password property.
     *
     * @param PropertyInterface $prop  The property to retrieve input from.
     * @param string            $label The input label.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function passwordInput(PropertyInterface $prop, $label)
    {
        $climate = $this->climate();

        $input = $climate->password($label);

        return $input;
    }

    /**
     * Set the base URI of the application.
     *
     * @param UriInterface|string $uri The base URI.
     * @return self
     */
    private function setBaseUrl($uri)
    {
        $this->baseUrl = $uri;

        return $this;
    }

        /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return void
     */
    private function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        ;
    }

}
