<?php

namespace Charcoal\Admin\Docs\Widget;

use Charcoal\Admin\Widget\FormPropertyWidget;

/**
 * DocFormPropertyWidget
 */
class DocFormPropertyWidget extends FormPropertyWidget
{
    /**
     * @var array
     */
    protected $extraData;

    /**
     * @var array
     */
    protected $displayOptions;

    /**
     * @return boolean
     */
    public function hasExtraData()
    {
        return !!count($this->extraData());
    }

    /**
     * @return boolean
     */
    public function collapsible()
    {
        $displayOps = $this->displayOptions();
        return isset($displayOps['collapsible']) ? $displayOps['collapsible'] : false;
    }

    /**
     * @return boolean
     */
    public function collapsed()
    {
        $displayOps = $this->displayOptions();
        return isset($displayOps['collapsed']) ? $displayOps['collapsed'] : false;
    }

    /**
     * @return boolean
     */
    public function parented()
    {
        $displayOps = $this->displayOptions();
        return isset($displayOps['parented']) ? $displayOps['parented'] : false;
    }

    /**
     * @return \Charcoal\Translator\Translation|null|string
     */
    public function typeDescription()
    {
        $type = $this->prop()->type();

        switch ($type) {
            case 'boolean':
                return $this->translator()->translation('
                    The field is a TRUE | FALSE statement
                ');
            case 'image':
            case 'audio':
            case 'file':
                return $this->translator()->translation('
                    The field will ask to upload a file using the file manager
                ');
            case 'string':
            case 'text':
                return $this->translator()->translation('
                    The field is a simple text input
                ');
            case 'object':
                return $this->translator()->translation('
                    The field is a relation to another object in the back-end (ex: a category object)
                ');
            case 'date-time':
                return $this->translator()->translation('
                    The field requires a date and will prompt a date picker<br>
                    as an easy way to provide it in a supported format
                ');
            default:
                return '';
        }
    }

    /**
     * @return array
     */
    public function extraData()
    {
        if (isset($this->extraData)) {
            return $this->extraData;
        }

        $prop = $this->prop();
        $out = [];

        if ($prop->l10n()) {
            $out[] = [
                'feature'     => $this->translator()->translation('multilingual'),
                'description' => $this->translator()->translation('
                    The field needs to be filled in more than one language
                ')
            ];
        }

        if ($prop->multiple()) {
            $out[] = [
                'feature'     => $this->translator()->translation('multiple'),
                'description' => $this->translator()->translation('
                    The field accepts more than one input
                ')
            ];
        }

        if ($prop->required()) {
            $out[] = [
                'feature'     => $this->translator()->translation('required'),
                'description' => $this->translator()->translation('
                    The field is required and will prevent saving or updating if empty
                ')
            ];
        }

        return $out;
    }

    /**
     * @return array
     */
    public function displayOptions()
    {
        return $this->displayOptions;
    }

    /**
     * @param array $displayOptions
     * @return self
     */
    public function setDisplayOptions($displayOptions)
    {
        $this->displayOptions = $displayOptions;

        return $this;
    }
}
