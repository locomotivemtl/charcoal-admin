<?php

namespace Charcoal\Admin\Widget;

use ReflectionClass;
use InvalidArgumentException;
use UnexpectedValueException;

// From Pimple
use Pimple\Container;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Docs\Widget\DocFormPropertyWidget;
use Charcoal\Admin\Ui\FormSidebarInterface;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;
use Charcoal\Admin\Widget\FormWidget;

/**
 * Object Admin Form
 */
class DocWidget extends FormWidget implements
    ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * @var string
     */
    protected $formIdent;

    /**
     * @var array
     */
    protected $formData;

    /**
     * @var boolean
     */
    protected $showHeader;

    /**
     * @var boolean
     */
    protected $showTitle;

    /**
     * The class name of the form property widget.
     *
     * @var string
     */
    protected $formPropertyClass = DocFormPropertyWidget::class;

    /**
     * Display options.
     *
     * @var array
     */
    private $displayOptions;

    /**
     * @param Container $container The DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Fill ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * For doc, disable tab view
     *
     * @return boolean
     */
    public function isTabbable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function widgetType()
    {
        return 'charcoal/admin/widget/doc';
    }

    /**
     * Retrieve the default label for the form submission button.
     *
     * @return Translation|string|null
     */
    public function defaultBackToObjectLabel()
    {
        if ($this->objId()) {
            return $this->translator()->translation('Update');
        }

        return parent::defaultSubmitLabel();
    }

    /**
     * @param array $data The widget data.
     * @return ObjectForm Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (!$this->mergedDataSources) {
            $this->mergeDataSources($data);
            $this->mergedDataSources = true;
        }

        return $this;
    }

    /**
     * @return FormSidebarInterface[]|\Generator
     */
    public function sidebars()
    {
        $objId = $this->obj()->id();

        if ($objId) {
            $translator = $this->translator();

            $template = 'charcoal/admin/widget/form.sidebar';
            $this->setDynamicTemplate('widget_template', $template);

            $metadata = $this->obj()->metadata();
            $objType  = (isset($metadata['labels']['singular_name'])
                        ? $translator->translate($metadata['labels']['singular_name'])
                        : (new ReflectionClass($obj))->getShortName());

            $label = $translator->translate('Back to {{name}} id: {{id}}');
            $label = strtr($label, [
                '{{name}}' => $objType ?: $this->obj()['obj_type'],
                '{{id}}'   => $this->obj()->id()
            ]);

            $url = 'object/edit?main_menu='.$this->obj()['main_menu'].'&obj_type={{obj_type}}&obj_id={{id}}';

            $out = [
                'title'       => $translator->translate('Actions'),
                'show_footer' => false,
                'actions'     => [
                    [
                        'label' => $label,
                        'url'   => $url
                    ]
                ]
            ];

            /** @var FormSidebarWidget $sidebar */
            $sidebar = $this->widgetFactory()->create(FormSidebarWidget::class);
            $sidebar->setViewController($this->viewController());
            $sidebar->setData($out);

            yield $sidebar;
        }
    }

    /**
     * Set the key for the form structure to use.
     *
     * @param  string $formIdent The form identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return self
     */
    public function setFormIdent($formIdent)
    {
        if (!is_string($formIdent)) {
            throw new InvalidArgumentException(
                'Form identifier must be a string'
            );
        }

        $this->formIdent = $formIdent;

        return $this;
    }

    /**
     * Retrieve a key for the form structure to use.
     *
     * If the form key is undefined, resolve a fallback.
     *
     * @return string
     */
    public function formIdentFallback()
    {
        $metadata = $this->obj()->metadata();

        if (isset($metadata['admin']['defaultForm'])) {
            return $metadata['admin']['defaultForm'];
        } elseif (isset($metadata['admin']['default_form'])) {
            return $metadata['admin']['default_form'];
        }

        return '';
    }

    /**
     * Retrieve the display options for the widget.
     *
     * @return array
     */
    public function displayOptions()
    {
        if (!$this->displayOptions) {
            $this->setDisplayOptions([]);
        }

        return $this->displayOptions;
    }

    /**
     * Set the display options for the widget.
     *
     * @param  array $options Display configuration.
     * @throws \RuntimeException If the display options are not an associative array.
     * @return self
     */
    public function setDisplayOptions(array $options)
    {
        if (!is_array($options)) {
            throw new \RuntimeException('The display options must be an associative array.');
        }

        $this->displayOptions = array_merge($this->defaultDisplayOptions(), $options);

        return $this;
    }

    /**
     * Retrieve the default display options for the widget.
     *
     * @return array
     */
    public function defaultDisplayOptions()
    {
        return [
            'parented'    => false,
            'collapsible' => false,
            'collapsed'   => false
        ];
    }

    /**
     * Retrieve the key for the form structure to use.
     *
     * @return string
     */
    public function formIdent()
    {
        return $this->formIdent;
    }

    /**
     * @param string $url The next URL.
     * @throws InvalidArgumentException If argument is not a string.
     * @return ActionInterface Chainable
     */
    public function setNextUrl($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'URL needs to be a string'
            );
        }

        $obj = $this->obj();
        if ($obj && $this->isObjRenderable($obj)) {
            $url = $obj->render($url);
        }

        $this->nextUrl = $url;
        return $this;
    }

    /**
     * Form action (target URL)
     *
     * @return string Relative URL
     */
    public function action()
    {
        $action = parent::action();

        if (!$action) {
            $obj = $this->obj();
            $objId = $obj->id();
            if ($objId) {
                return 'object/edit';
            } else {
                return 'object/collection';
            }
        } else {
            return $action;
        }
    }

    /**
     * Retrieve the object's properties as form controls.
     *
     * @param  array $group An optional group to use.
     * @throws UnexpectedValueException If a property data is invalid.
     * @return DocFormPropertyWidget[]|Generator
     */
    public function formProperties(array $group = null)
    {
        $obj   = $this->obj();
        $props = $obj->metadata()->properties();

        // We need to sort form properties by form group property order if a group exists
        if (!empty($group)) {
            $group = array_map([ $this, 'camelize' ], $group);
            $group = array_flip($group);
            $props = array_intersect_key($props, $group);
            $props = array_merge($group, $props);
        }

        foreach ($props as $propertyIdent => $propertyMetadata) {
            $propertyIdent = $this->camelize($propertyIdent);
            if (method_exists($obj, 'filterPropertyMetadata')) {
                $propertyMetadata = $obj->filterPropertyMetadata($propertyMetadata, $propertyIdent);
            }

            if (!is_array($propertyMetadata)) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid property data for "%1$s", received %2$s',
                    $propertyIdent,
                    (is_object($propertyMetadata) ? get_class($propertyMetadata) : gettype($propertyMetadata))
                ));
            }

            $propertyMetadata['display_options'] = $this->displayOptions();

            $formProperty = $this->createFormProperty();
            $formProperty->setViewController($this->viewController());
            $formProperty->setPropertyIdent($propertyIdent);
            $formProperty->setData($propertyMetadata);
            $formProperty->setPropertyVal($obj[$propertyIdent]);

            if ($formProperty->hidden()) {
                $this->hiddenProperties[$propertyIdent] = $formProperty;
            } else {
                yield $propertyIdent => $formProperty;
            }
        }
    }

    /**
     * Retrieve an object property as a form control.
     *
     * @param  string $propertyIdent An optional group to use.
     * @throws InvalidArgumentException If the property identifier is not a string.
     * @throws UnexpectedValueException If a property data is invalid.
     * @return DocFormPropertyWidget
     */
    public function formProperty($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property ident must be a string'
            );
        }

        $propertyMetadata = $this->obj()->metadata()->property($propertyIdent);

        if (!is_array($propertyMetadata)) {
            throw new UnexpectedValueException(sprintf(
                'Invalid property data for "%1$s", received %2$s',
                $propertyIdent,
                (is_object($propertyMetadata) ? get_class($propertyMetadata) : gettype($propertyMetadata))
            ));
        }

        $p = $this->createFormProperty();
        $p->setViewController($this->viewController());
        $p->setPropertyIdent($propertyIdent);
        $p->setData($propertyMetadata);

        return $p;
    }

    /**
     * Set the form's auxiliary data.
     *
     * This method is called via {@see self::setData()} if a "form_data" parameter
     * is present on the HTTP request.
     *
     * @param array $data Data.
     * @return ObjectFormWidget Chainable.
     */
    public function setFormData(array $data)
    {
        $objData = $this->objData();
        $merged = array_replace_recursive($objData, $data);

        // Remove null values
        $merged = array_filter($merged, function ($val) {
            if ($val === null) {
                return false;
            }

            return true;
        });

        $this->formData = $merged;
        $this->obj()->setData($merged);

        return $this;
    }

    /**
     * Retrieve the form's auxiliary  data.
     *
     * @return array
     */
    public function formData()
    {
        if (!$this->formData) {
            $this->formData = $this->objData();
        }

        return $this->formData;
    }

    /**
     * Object data.
     * @return array Object data.
     */
    public function objData()
    {
        return $this->obj()->data();
    }

    /**
     * @return boolean
     */
    public function showHeader()
    {
        return $this->showHeader;
    }

    /**
     * @param boolean $showHeader Is the Header to be shown.
     * @return self
     */
    public function setShowHeader($showHeader)
    {
        $this->showHeader = $showHeader;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showTitle()
    {
        return $this->showTitle;
    }

    /**
     * @param boolean $showTitle Is the title to be shown.
     * @return self
     */
    public function setShowTitle($showTitle)
    {
        $this->showTitle = $showTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/admin/docs/widget/form-group/doc';
    }

    /**
     * Retrieve the default data sources (when setting data on an entity).
     *
     * @return string[]
     */
    protected function defaultDataSources()
    {
        return [ static::DATA_SOURCE_REQUEST, static::DATA_SOURCE_OBJECT ];
    }

    /**
     * Retrieve the default data source filters (when setting data on an entity).
     *
     * @return array
     */
    protected function defaultDataSourceFilters()
    {
        return [
            'request' => null,
            'object'  => 'array_replace_recursive'
        ];
    }

    /**
     * Retrieve the default data source filters (when setting data on an entity).
     *
     * Note: Adapted from {@see \Slim\CallableResolver}.
     *
     * @link   https://github.com/slimphp/Slim/blob/3.x/Slim/CallableResolver.php
     * @param  mixed $toResolve A callable used when merging data.
     * @return callable|null
     */
    protected function resolveDataSourceFilter($toResolve)
    {
        if (is_string($toResolve)) {
            $obj = $this->obj();

            $resolved = [ $obj, $toResolve ];

            // Check for Slim callable
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];

                if ($class === 'parent') {
                    $resolved = [ $obj, $class.'::'.$method ];
                }
            }

            $toResolve = $resolved;
        }

        return parent::resolveDataSourceFilter($toResolve);
    }

    /**
     * Retrieve the accepted metadata from the current request.
     *
     * @return array
     */
    protected function acceptedRequestData()
    {
        return array_merge(
            [ 'obj_type', 'obj_id', 'template' ],
            parent::acceptedRequestData()
        );
    }

    /**
     * Fetch metadata from the current object type.
     *
     * @return array
     */
    protected function dataFromObject()
    {
        $obj = $this->obj();
        $objMetadata = $obj->metadata();
        $adminMetadata = (isset($objMetadata['admin']) ? $objMetadata['admin'] : null);

        $formIdent = $this->formIdent();
        if (!$formIdent) {
            $formIdent = $this->formIdentFallback();
        }

        if ($formIdent && $this->isObjRenderable($obj)) {
            $formIdent = $obj->render($formIdent);
        }

        if (isset($adminMetadata['forms'][$formIdent])) {
            $objFormData = $adminMetadata['forms'][$formIdent];
        } else {
            $objFormData = [];
        }

        if (isset($objFormData['groups']) && isset($adminMetadata['form_groups'])) {
            $extraFormGroups = array_intersect(
                array_keys($adminMetadata['form_groups']),
                array_keys($objFormData['groups'])
            );
            foreach ($extraFormGroups as $groupIdent) {
                $objFormData['groups'][$groupIdent] = array_replace_recursive(
                    $adminMetadata['form_groups'][$groupIdent],
                    $objFormData['groups'][$groupIdent]
                );
            }
        }

        if (isset($objFormData['sidebars']) && isset($adminMetadata['form_sidebars'])) {
            $extraFormSidebars = array_intersect(
                array_keys($adminMetadata['form_sidebars']),
                array_keys($objFormData['sidebars'])
            );
            foreach ($extraFormSidebars as $sidebarIdent) {
                $objFormData['sidebars'][$sidebarIdent] = array_replace_recursive(
                    $adminMetadata['form_sidebars'][$sidebarIdent],
                    $objFormData['sidebars'][$sidebarIdent]
                );
            }
        }

        return $objFormData;
    }

    /**
     * Create a new form group widget.
     *
     * @see    \Charcoal\Ui\Form\FormTrait::createFormGroup()
     * @param  array|null $data Optional. The form group data to set.
     * @return FormGroupInterface
     */
    protected function createFormGroup(array $data = null)
    {
        $type = $this->defaultGroupType();

        if (isset($data['template'])) {
            unset($data['template']);
        }

        $group = $this->formGroupFactory()->create($type);
        $group->setForm($this);

        if ($group instanceof ObjectContainerInterface) {
            if (empty($group->objType())) {
                $group->setObjType($this->objType());
            }

            if (empty($group->objId()) && !empty($this->objId())) {
                $group->setObjId($this->objId());
            }
        }

        if ($data !== null) {
            $group->setData($data);
        }

        $group['show_header'] = $this->showHeader();
        $group['show_title'] = $this->showTitle();

        return $group;
    }

    /**
     * Update the given form group widget.
     *
     * @see    \Charcoal\Ui\Form\FormTrait::updateFormGroup()
     * @param  FormGroupInterface $group      The form group to update.
     * @param  array|null         $groupData  Optional. The new group data to apply.
     * @param  string|null        $groupIdent Optional. The new group identifier.
     * @return FormGroupInterface
     */
    protected function updateFormGroup(
        FormGroupInterface $group,
        array $groupData = null,
        $groupIdent = null
    ) {
        $group->setForm($this);

        if ($groupIdent !== null) {
            $group->setIdent($groupIdent);
        }

        if ($group instanceof ObjectContainerInterface) {
            if (empty($group->objType())) {
                $group->setObjType($this->objType());
            }

            if (empty($group->objId()) && !empty($this->objId())) {
                $group->setObjId($this->objId());
            }
        }

        if ($groupData !== null) {
            $group->setData($groupData);
        }

        return $group;
    }
}
