<?php

namespace Charcoal\Admin\Property\Input;

// Dependency from 'charcoal-view'
use \Charcoal\View\ViewableInterface;

// Local Dependency
use \Charcoal\Admin\Property\Input\FileInput;

/**
 * Image Property Input
 */
class ImageInput extends FileInput
{
    /**
     * @return string|null
     */
    public function filePreview()
    {
        $value = $this->inputVal();
        $html  = '';

        if ($value && $this instanceof ViewableInterface && $this->view() !== null) {
            if (parse_url($value, PHP_URL_SCHEME)) {
                $tpl = '<img src="{{ inputVal }}" style="max-width: 100%">';
            } else {
                $tpl = '<img src="{{ baseUrl }}{{ inputVal }}" style="max-width: 100%">';
            }

            $html = $this->view()->render($tpl, $this);
        }

        return $html;
    }

    /**
     * Retrieve the default label for the file picker button.
     *
     * @return string[]
     */
    protected function defaultChooseButtonLabel()
    {
        return [
            'en' => 'Choose Image…',
            'fr' => 'Choisissez une image…'
        ];
    }

    /**
     * Retrieve the default label for the file removal button.
     *
     * @return string[]
     */
    protected function defaultRemoveButtonLabel()
    {
        return [
            'en' => 'Remove Image',
            'fr' => 'Retirer l’image'
        ];
    }
}
