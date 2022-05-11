<?php

namespace Charcoal\Admin\Property;

use Traversable;
use InvalidArgumentException;
use UnexpectedValueException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\DescribableInterface;
use Charcoal\Model\DescribableTrait;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;
use Charcoal\Translator\TranslatorAwareTrait;

// From 'charcoal-property'
use Charcoal\Property\PropertyFactory;
use Charcoal\Property\PropertyInterface as ModelPropertyInterface;
use Charcoal\Property\PropertyMetadata;

// From 'charcoal-app'
use Charcoal\App\DebugAwareTrait;

// From 'charcoal-admin'
use Charcoal\Admin\Property\PropertyInterface as AdminPropertyInterface;

/**
 * Base Admin model property decorator
 */
abstract class AbstractProperty implements
    AdminPropertyInterface,
    DescribableInterface,
    LoggerAwareInterface,
    ViewableInterface
{
    use DebugAwareTrait;
    use DescribableTrait;
    use LoggerAwareTrait;
    use TranslatorAwareTrait;
    use ViewableTrait;

    const DEFAULT_ESCAPE_FUNCTION = 'htmlspecialchars';

    /**
     * @var ModelPropertyInterface $property
     */
    private $property;

    /**
     * @var array $propertyData
     */
    private $propertyData = [];

    /**
     * @var mixed $propertyVal
     */
    private $propertyVal;

    /**
     * @var string $lang
     */
    private $lang;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var boolean $multiple
     */
    protected $multiple;

    /**
     * Holds a list of all renderable classes.
     *
     * Format: `class => boolean`
     *
     * @var boolean[]
     */
    protected static $objRenderableCache = [];

    /**
     * @param array|\ArrayAccess $data Constructor data.
     */
    public function __construct($data = null)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }

        $this->setLogger($data['logger']);

        if (isset($data['metadata_loader'])) {
            $this->setMetadataLoader($data['metadata_loader']);
        }

        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * This function takes an array and fill the model object with its value.
     *
     * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
     *
     * For example, calling with `setData(['properties'=>$properties])` would call
     * `setProperties($properties)`, becasue `setProperties()` exists.
     *
     * But calling with `setData(['foobar'=>$foo])` would set the `$foobar` member
     * on the metadata object, because the method `set_foobar()` does not exist.
     *
     * @param array $data The input data.
     * @return self
     */
    public function setData(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [ $this, $this->setter($prop) ];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }

        $this->propertyData = $data;

        return $this;
    }

    /**
     * @param  ModelPropertyInterface $property The model property.
     * @return self
     */
    public function setProperty(ModelPropertyInterface $property)
    {
        $this->property  = $property;
        $this->inputName = null;

        return $this;
    }

    /**
     * @return ModelPropertyInterface
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Alias of {@see self::getProperty()}
     *
     * @return mixed
     */
    public function property()
    {
        return $this->getProperty();
    }

    /**
     * Alias of {@see self::getProperty()}
     *
     * @return ModelPropertyInterface
     */
    public function p()
    {
        return $this->getProperty();
    }

    /**
     * Retrieve the model property identifier.
     *
     * @return string
     */
    public function propertyIdent()
    {
        return $this->p()->ident();
    }

    /**
     * @param mixed $val The property value.
     * @return PropertyInputInterface Chainable
     */
    public function setPropertyVal($val)
    {
        $this->propertyVal = $val;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasPropertyVal()
    {
        $propertyValue = $this->propertyVal();

        if (!is_scalar($propertyValue)) {
            if ($propertyValue instanceof Translation) {
                foreach ($propertyValue->data() as $translationValue) {
                    if ($translationValue !== null && $translationValue !== '') {
                        return true;
                    }
                }

                return false;
            }

            if (is_countable($propertyValue)) {
                return count($propertyValue) > 0;
            }

            return (bool)$propertyValue;
        }

        return ($propertyValue !== null && $propertyValue !== '');
    }

    /**
     * @return mixed
     */
    public function propertyVal()
    {
        return $this->propertyVal;
    }

    /**
     * @param string $lang The language code / ident.
     * @return PropertyInputInterface Chainable
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * Get the input language
     * @return string
     */
    public function lang()
    {
        if ($this->lang === null) {
            return $this->translator()->getLocale();
        }

        return $this->lang;
    }

    /**
     * @param string $ident Input identifier.
     * @throws InvalidArgumentException If the ident is not a string.
     * @return self
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Property Input identifier must be string'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Escapes the given value.
     *
     * This method should be extended in sub-classes.
     * If a valid "function" option is not passed,
     * this method does nothing.
     *
     * @param  string $val    The value to escape.
     * @param  array $options Optional escape options.
     * @throws InvalidArgumentException If the value to escape is not a string.
     * @return string
     */
    public function escapeVal($val, array $options = [])
    {
        if (!is_string($val)) {
            throw new InvalidArgumentException(
                'Expected string to escape'
            );
        }

        if (isset($options['function'])) {
            $escape  = $this->parseEscapeOptions($options);
            $options = [];
        } else {
            return $val;
        }

        $callback = $escape['function'];

        if (!isset($escape['parameters'])) {
            return $callback($val);
        }

        $args = $escape['parameters'];

        if (isset($options['parameters']) && is_array($options['parameters'])) {
            $args = array_replace($args, $options['parameters']);
        }

        return $callback($val, ...$args);
    }

    /**
     * Parses the raw escape options.
     *
     * @param  mixed $escape The escape function name, callback, options array,
     *     FALSE to disable, TRUE or NULL to use default function.
     * @throws InvalidArgumentException If the escape argument is invalid.
     * @return array|null
     */
    public function parseEscapeOptions($escape)
    {
        if ($escape === false) {
            return null;
        }

        if ($escape === true) {
            $escape = $this->getDefaultEscapeOptions();
        }

        if (is_string($escape) || is_callable($escape)) {
            $escape = [
                'function' => $escape,
            ];
        } elseif (!is_array($escape)) {
            throw new InvalidArgumentException(
                'Expected escape function name, function expression, or options array'
            );
        } elseif (!isset($escape['function'])) {
            throw new InvalidArgumentException(
                'Missing escape function name or expression'
            );
        }

        $this->assertValidEscapeFunction($escape['function']);

        if (is_string($escape['function'])) {
            $escape['function'] = $this->wrapEscapeFunction($escape['function']);
        }

        return $escape;
    }

    /**
     * Wraps the raw escape function.
     *
     * @param  callable $callback The escape function name or expression.
     * @throws InvalidArgumentException If the escape argument is invalid.
     * @return callable
     */
    protected function wrapEscapeFunction(callable $callback)
    {
        return function ($value) use ($callback) {
            return call_user_func_array($callback, func_get_args());
        };
    }

    /**
     * Asserts that the escape function is valid, throws an exception if not.
     *
     * @param  mixed $escape The escape function name or expression.
     * @throws InvalidArgumentException If an invalid function name or expression.
     * @return void
     */
    protected function assertValidEscapeFunction($escape)
    {
        if (is_string($escape)) {
            if (!function_exists($escape)) {
                throw new InvalidArgumentException(sprintf(
                    'Undefined escape function named "%s"',
                    $escape
                ));
            }
        }

        if (!is_callable($escape)) {
            throw new InvalidArgumentException(sprintf(
                'Expected escape function name or function expression, received "%s"',
                is_object($escape) ? get_class($escape) : gettype($escape)
            ));
        }
    }

    /**
     * Retrieve the default escape function and settings.
     *
     * @return array
     */
    public function getDefaultEscapeOptions()
    {
        return [
            'function' => static::DEFAULT_ESCAPE_FUNCTION,
        ];
    }

    /**
     * @param boolean $multiple Multiple flag.
     * @return self
     */
    public function setMultiple($multiple)
    {
        $this->multiple = !!$multiple;
        return $this;
    }

    /**
     * @return boolean
     */
    public function multiple()
    {
        return $this->multiple;
    }

    /**
     * Render the given template from string.
     *
     * @see    \Charcoal\Admin\Property\AbstractPropertyDisplay::renderTranslatableTemplate()
     * @see    \Charcoal\View\ViewableInterface::renderTemplate()
     * @param  mixed $templateString The template to render.
     * @return string The rendered template.
     */
    public function renderTranslatableTemplate($templateString)
    {
        if ($templateString instanceof Translation) {
            $origLang = $this->translator()->getLocale();
            foreach ($this->translator()->availableLocales() as $lang) {
                if (!isset($templateString[$lang])) {
                    continue;
                }
                $translation = $templateString[$lang];
                $isBlank = empty($translation) && !is_numeric($translation);
                if (!$isBlank) {
                    $this->translator()->setLocale($lang);
                    $translation = $this->renderTemplate($translation);
                    if ($translation !== null) {
                        $templateString[$lang] = $translation;
                    }
                }
            }
            $this->translator()->setLocale($origLang);
            $templateString->isRendered = true;

            return $templateString;
        } elseif (is_string($templateString)) {
            $isBlank = empty($templateString) && !is_numeric($templateString);
            if (!$isBlank) {
                return $this->renderTemplate($templateString);
            }
        }

        return '';
    }

    /**
     * Determine if the model implements {@see \Charcoal\View\ViewableInterface}.
     *
     * @see \Charcoal\Admin\Ui\ObjectContainerTrait::isObjRenderable()
     *
     * @param  string|object $obj      Object type or instance to test.
     * @param  boolean       $toString Whether to test for `__toString()`.
     * @return boolean
     */
    protected function isObjRenderable($obj, $toString = false)
    {
        if (is_string($obj)) {
            if (!method_exists($this, 'modelFactory')) {
                return false;
            }

            $obj = $this->modelFactory()->get($obj);
        }

        if (!is_object($obj)) {
            return false;
        }

        $key = get_class($obj);

        if (isset(static::$objRenderableCache[$key])) {
            return static::$objRenderableCache[$key];
        }

        $check = false;
        if (is_object($obj)) {
            if (($obj instanceof ViewableInterface) && ($obj->view() instanceof ViewInterface)) {
                $check = true;
            } elseif ($toString && is_callable([ $obj, '__toString()' ])) {
                $check = true;
            }
        }

        static::$objRenderableCache[$key] = $check;

        return static::$objRenderableCache[$key];
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        // Fullfills the DescribableTrait dependencies
        $this->setMetadataLoader($container['metadata/loader']);

        // Fulfills the TranslatorAwareTrait dependencies
        $this->setTranslator($container['translator']);

        // Fulfills the ViewableTrait dependencies
        $this->setView($container['view']);

        // Fulfills the DebugAwareTrait dependencies
        $this->setDebug($container['debug']);
    }

    /**
     * Create a new metadata object.
     *
     * @param  array $data Optional metadata to merge on the object.
     * @return PropertyMetadata
     */
    protected function createMetadata(array $data = null)
    {
        $class = $this->metadataClass();
        return new $class($data);
    }

    /**
     * Retrieve the class name of the metadata object.
     *
     * @return string
     */
    protected function metadataClass()
    {
        return PropertyMetadata::class;
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    protected function getter($key)
    {
        $getter = $key;
        return $this->camelize($getter);
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelCase string.
     */
    private function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
