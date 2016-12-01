<?php

namespace Charcoal\Admin\Widget\FormGroup;

use \RuntimeException;
use \UnexpectedValueException;
use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From 'charcoal-property'
use \Charcoal\Property\PropertyInterface;

// From 'charcoal-ui'
use \Charcoal\Ui\FormGroup\AbstractFormGroup;

// From 'charcoal-admin'
use \Charcoal\Admin\Widget\FormGroupWidget;
use \Charcoal\Admin\Ui\ObjectContainerInterface;

/**
 * Form Group Structure Property
 *
 * The form group widget displays a set of form controls based on properties
 * assigned to the widget directly or a proxy structure property.
 *
 * ## Examples
 *
 * **Example #1 — Structure widget**
 *
 * ```json
 * "properties": {
 *     "extra_data": {
 *         "type": "structure",
 *         "structure_data": {
 *             "properties": { … },
 *             "admin": {
 *                 "form_group": { … }
 *             }
 *         }
 *     }
 * },
 * "widgets": [
 *     {
 *         "title": "Extra Data",
 *         "type": "charcoal/admin/widget/form-group/structure",
 *         "template": "charcoal/admin/widget/form-group/structure",
 *         "storage_property": "extra_data"
 *     }
 * ]
 * ```
 *
 * **Example #2 — With verbose storage declaration**
 *
 * {@todo Eventually, the form group could support other storage sources such as
 * file-based or a database such as an SQL server.}
 *
 * ```json
 * {
 *     "title": "Extra Data",
 *     "type": "charcoal/admin/widget/form-group/structure",
 *     "template": "charcoal/admin/widget/form-group/structure",
 *     "storage": {
 *         "type": "property",
 *         "property": "extra_data"
 *     }
 * }
 * ```
 *
 */
class StructureFormGroup extends FormGroupWidget
{
    /**
     * The form group's storage medium.
     *
     * @var array|PropertyInterface|SourceInterface|null
     */
    protected $storage;

    /**
     * The form group's storage target. {@deprecated In favor of $storage.}
     *
     * @var PropertyInterface|null
     */
    private $storageProperty;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);
    }

    /**
     * Retrieve the form's object.
     *
     * @return \Charcoal\Model\ModelInterface
     */
    public function obj()
    {
        if (!$this->form() instanceof ObjectContainerInterface) {
            throw new RuntimeException(
                sprintf('The form must implement %s', ObjectContainerInterface::class)
            );
        }

        return $this->form()->obj();
    }

    /**
     * Set the form group's storage target.
     *
     * Must be a property of the form's object model that will receive an associative array
     * of the form group's data.
     *
     * @param  string|PropertyInterface $propertyIdent The property identifier—or instance—of a storage property.
     * @throws InvalidArgumentException If the property identifier is not a string.
     * @throws UnexpectedValueException If a property data is invalid.
     * @return StructureFormGroup
     */
    public function setStorageProperty($propertyIdent)
    {
        $property = null;
        if ($propertyIdent instanceof PropertyInterface) {
            $property      = $propertyIdent;
            $propertyIdent = $property->ident();
        } elseif (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property identifier must be a string'
            );
        }

        $obj = $this->obj();
        if (!$obj->hasProperty($propertyIdent)) {
            throw new UnexpectedValueException(
                sprintf(
                    'The "%1$s" property is not defined on [%2$s]',
                    $propertyIdent,
                    get_class($this->obj())
                )
            );
        }

        if ($property === null) {
            $property = $obj->property($propertyIdent);
        }

        $this->storageProperty = $property;

        $struct = $property->structureData();
        if (isset($struct['admin']['form_group'])) {
            $widgetData = $this->data();
            $this->setData($struct['admin']['form_group']);
            $this->setData($widgetData);
        }

        return $this;
    }

    /**
     * Retrieve the form group's storage property master.
     *
     * @return PropertyInterface
     */
    public function storageProperty()
    {
        if ($this->storageProperty === null) {
            throw new RuntimeException(
                sprintf('Storage property owner is not defined for "%s"', get_class($this))
            );
        }

        return $this->storageProperty;
    }

    /**
     * Retrieve the properties from the storage property's structure.
     *
     * @return array
     */
    public function structProperties()
    {
        $struct = $this->storageProperty()->structureData();

        if (isset($struct['properties'])) {
            return $struct['properties'];
        }

        return [];
    }

    /**
     * Retrieve the object's properties from the form.
     *
     * @return mixed|Generator
     */
    public function formProperties()
    {
        $master = $this->storageProperty();
        $form   = $this->form();
        $obj    = $this->obj();
        $entry  = $obj[$master->ident()];

        $propertyIdentPattern = ($master->multiple() ? '%1$s['.uniqid().'][%2$s]' : '%1$s[%2$s]' );

        $propPreferences  = $this->propertiesOptions();
        $groupProperties  = $this->groupProperties();
        $structProperties = $this->structProperties();
        if ($groupProperties) {
            $structProperties = array_merge(array_flip($groupProperties), $structProperties);
        }

        foreach ($structProperties as $propertyIdent => $propertyMetadata) {
            if (method_exists($obj, 'filterPropertyMetadata')) {
                $propertyMetadata = $obj->filterPropertyMetadata($propertyMetadata, $propertyIdent);
            }

            if (!is_array($propertyMetadata)) {
                throw new UnexpectedValueException(
                    sprintf(
                        'Invalid property data for "%1$s", received %2$s',
                        $propertyIdent,
                        (is_object($propertyMetadata) ? get_class($propertyMetadata) : gettype($propertyMetadata))
                    )
                );
            }

            $subPropertyIdent = sprintf($propertyIdentPattern, $master->ident(), $propertyIdent);

            $formProperty = $form->createFormProperty();
            $formProperty->setViewController($this->viewController());
            $formProperty->setPropertyIdent($subPropertyIdent);
            $formProperty->setData($propertyMetadata);

            if (!empty($propPreferences[$propertyIdent])) {
                $propertyOptions = $propPreferences[$propertyIdent];

                if (is_array($propertyOptions)) {
                    $formProperty->setData($propertyOptions);
                }
            }

            if ($obj) {
                $val = $entry[$propertyIdent];
                $formProperty->setPropertyVal($val);
            }

            if (!$formProperty->l10nMode()) {
                $formProperty->setL10nMode($this->l10nMode());
            }

            yield $propertyIdent => $formProperty;
        }
    }
}
