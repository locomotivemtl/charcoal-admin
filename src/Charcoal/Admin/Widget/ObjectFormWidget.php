<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;
use \Exception;

use \Charcoal\Admin\Widget\FormWidget;
use \Charcoal\Admin\Widget\FormPropertyWidget;

use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 *
 */
class ObjectFormWidget extends FormWidget implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * @var string
     */
    protected $formIdent;

    /**
     * @return string
     */
    public function widgetType()
    {
        return 'charcoal/admin/widget/objectForm';
    }

    /**
     * @param array $data The widget data
     * @return ObjectForm Chainable
     */
    public function setData(array $data)
    {
        // @TODO Remove once RequirementContainer is implemented
        // Needed this to be able to output {{objId}}
        $data = array_merge($_GET, $data);

        parent::setData($data);
        # $this->setObjData($data);

        /*if (isset($data['form_ident']) && $data['form_ident'] !== null) {
            $this->setFormIdent($data['form_ident']);
        }*/

        $objData = $this->dataFromObject();
        $data = array_merge_recursive($objData, $data);

        parent::setData($data);

        return $this;
    }

     /**
      * @param string $url The next URL.
      * @throws InvalidArgumentException if success is not a boolean
      * @return ActionInterface Chainable
      */
    public function setNextUrl($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(
                'URL needs to be a string'
            );
        }

        if (!$this->obj()) {
            $this->nextUrl = $url;
            return $this;
        }

        $this->nextUrl = $this->obj()->render($url);
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
                return 'action/object/update';
            } else {
                return 'action/object/save';
            }
        } else {
            return $action;
        }
    }

    /**
     * @param string $formIdent The form ident.
     * @throws InvalidArgumentException
     * @return ObjectForm Chainable
     */
    public function setFormIdent($formIdent)
    {
        if (!is_string($formIdent)) {
            throw new InvalidArgumentException(
                'Form ident must be a string'
            );
        }
        $this->formIdent = $formIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function formIdent()
    {
        return $this->formIdent;
    }

    /**
     * Set the data from an object.
     *
     * @return array
     */
    public function dataFromObject()
    {
        $obj = $this->obj();
        $metadata = $obj->metadata();
        $admin_metadata = isset($metadata['admin']) ? $metadata['admin'] : null;
        $formIdent = $this->formIdent();
        if (!$formIdent) {
            $formIdent = isset($admin_metadata['default_form']) ? $admin_metadata['default_form'] : '';
        }

        $objFormData = isset($admin_metadata['forms'][$formIdent]) ? $admin_metadata['forms'][$formIdent] : [];
        return $objFormData;
    }

    /**
     * FormProperty Generator
     *
     * @todo Merge with property_options
     */
    public function formProperties(array $group = null)
    {
        $obj = $this->obj();
        $props = $obj->metadata()->properties();

        // We need to sort form properties by form group property order if a group exists
        if (!empty($group)) {
            $props = array_merge(array_flip($group), $props);
        }

        foreach ($props as $propertyIdent => $property) {
            if (!is_array($property)) {
                throw new Exception(
                    sprintf(
                        'Invalid property data for "%1$s", received %2$s',
                        $propertyIdent,
                        (is_object($property) ? get_class($property) : gettype($property))
                    )
                );
            }
            $p = new FormPropertyWidget([
                'logger' => $this->logger
            ]);
            $p->setPropertyIdent($propertyIdent);
            $p->setData($property);
            yield $propertyIdent => $p;
        }
    }

    /**
     * @return array
     */
    public function formData()
    {
        $obj = $this->obj();
        $formData = $obj->data();
        return $formData;
    }
}
