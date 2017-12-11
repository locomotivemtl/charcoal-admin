<?php

namespace Charcoal\Admin;

// From Pimple
use Pimple\Container;

// From 'league/climate'
use League\CLImate\TerminalObject\Dynamic\Input as LeagueInput;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-app'
use Charcoal\App\Script\AbstractScript;

// From 'charcoal-property'
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
     * The model factory.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        $this->setModelFactory($container['model/factory']);
        $this->setTranslator($container['translator']);
        $this->setBaseUrl($container['base-url']);

        parent::setDependencies($container);
    }


    /**
     * Retrieve the model factory.
     *
     * @return FactoryInterface
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
     * @param  PropertyInterface $prop  The property to retrieve input from.
     * @param  string|null       $label Optional. The input label.
     * @return LeagueInput The League's terminal input object.
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

        if ($prop->type() === 'password') {
            return $this->passwordInput($prop, $label);
        } elseif ($prop->type() === 'boolean') {
            return $this->booleanInput($prop, $label);
        } else {
            $input = $climate->input($label);
            if ($prop->type() === 'text' || $prop->type === 'html') {
                $input->multiLine();
            }
        }
        return $input;
    }

    /**
     * Get a CLI input from a boolean property.
     *
     * @param  PropertyInterface $prop  The property to retrieve input from.
     * @param  string            $label The input label.
     * @return LeagueInput The League's terminal input object.
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
     * @param  PropertyInterface $prop  The property to retrieve input from.
     * @param  string            $label The input label.
     * @return LeagueInput The League's terminal input object.
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
     * @param  UriInterface|string $uri The base URI.
     * @return void
     */
    private function setBaseUrl($uri)
    {
        $this->baseUrl = $uri;
    }

    /**
     * Set the model factory.
     *
     * @param  FactoryInterface $factory The factory used to create models.
     * @return void
     */
    private function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
    }
}
