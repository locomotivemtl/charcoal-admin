<?php

namespace Charcoal\Admin\Widget;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\ObjectFormWidget;

/**
 * The quick form widget for editing objects on the go.
 */
class QuickFormWidget extends ObjectFormWidget
{
    /**
     * Retrieve the identifier of the form to use, or its fallback.
     *
     * @see    ObjectFormWidget::formIdentFallback()
     * @return string
     */
    public function formIdentFallback()
    {
        $metadata = $this->obj()->metadata();

        if (isset($metadata['admin']['default_quick_form'])) {
            return $metadata['admin']['default_quick_form'];
        }

        if (isset($this->data()['form_data']['form_ident'])) {
            $ident = $this->data()['form_data']['form_ident'];

            if (is_string($ident) && !empty($ident)) {
                return $ident;
            }
        }

        return 'quick';
    }

    /**
     * Retrieve the label for the form submission button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function submitLabel()
    {
        if (isset($this->data()['form_data']['submit_label'])) {
            $label = $this->data()['form_data']['submit_label'];
            $this->submitLabel = $this->translator()->translation($label);
        }

        return parent::submitLabel();
    }
}
