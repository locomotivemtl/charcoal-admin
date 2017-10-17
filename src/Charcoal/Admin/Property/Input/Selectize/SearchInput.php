<?php

namespace Charcoal\Admin\Property\Input\Selectize;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\SelectInput;

/**
 * Searchable Input Selectize
 */
class SearchInput extends SelectInput
{
    /**
     * @var array
     */
    private $selectizeOptions = [];

    /**
     * Plugin options
     * @return array Selectize plugin options (js).
     */
    public function selectizeOptions()
    {
        return $this->selectizeOptions;
    }

    /**
     * Set the selectize picker's options.
     *
     * This method overwrites existing helpers.
     *
     * @param  array $settings The selectize picker options.
     * @return TagsInput Chainable
     */
    public function setSelectizeOptions(array $settings)
    {
        $this->selectizeOptions = $settings;

        return $this;
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

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        $prop = $this->property();

        $data = [
            // Selectize Control
            'title'                    => (string)$prop->label(),
            'copy_items'               => $this->allowClipboardCopy(),

            'selectize_selector'       => '#'.$this->inputId(),
            'selectize_options'        => $this->selectizeOptions(),

            // Base Property
            'required'                 => $this->required(),
            'l10n'                     => $this->property()->l10n(),
            'multiple'                 => $this->multiple(),
            'multiple_separator'       => $this->property()->multipleSeparator(),
            'multiple_options'         => $this->property()->multipleOptions(),
        ];

        if ($prop instanceof ObjectProperty) {
            if ($prop->objType()) {
                $data['pattern']  = $prop->pattern();
                $data['obj_type'] = $prop->objType();
            }
        }

        return $data;
    }
}
