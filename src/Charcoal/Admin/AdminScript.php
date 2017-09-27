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
        $this->modelFactory = $container['model/factory'];
        $this->setTranslator($container['translator']);

        $this->setBaseUrl($container['base-url']);

        parent::setDependencies($container);
    }

    /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return AdminScript Chainable
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory()
    {
        return $this->modelFactory;
    }

    /**
     * Set the base URI of the application.
     *
     * @param UriInterface|string $uri The base URI.
     * @return self
     */
    public function setBaseUrl($uri)
    {
        $this->baseUrl = $uri;

        return $this;
    }

    /**
     * Retrieve the base URI of the application.
     *
     * @return UriInterface|string
     */
    public function baseUrl()
    {
        return rtrim($this->baseUrl, '/').'/';
    }


    /**
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return mixed
     */
    protected function propertyToInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        if ($prop->type() == 'password') {
            return $this->passwordInput($prop);
        } elseif ($prop->type() == 'boolean') {
            return $this->booleanInput($prop);
        } else {
            $input = $climate->input(sprintf(
                'Enter value for "%s":',
                $prop->label()
            ));
            if ($prop->type() == 'text' || $prop->type == 'html') {
                $input->multiLine();
            }
        }
        return $input;
    }

    /**
     * Get a CLI input from a boolean property.
     *
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function booleanInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        $opts = [
            1 => $prop->trueLabel(),
            0 => $prop->falseLabel()
        ];
        $input = $climate->radio(
            sprintf('Enter value for "%s":', $prop->label()),
            $opts
        );
        return $input;
    }

    /**
     * Get a CLI password input (hidden) from a password property.
     *
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function passwordInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        $input = $climate->password(sprintf(
            'Enter value for "%s":',
            $prop->label()
        ));

        return $input;
    }
}
