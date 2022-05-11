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

        if (isset($this->formData()['form_ident'])) {
            $ident = $this->formData()['form_ident'];

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
        if (isset($this->formData()['submit_label'])) {
            $label = $this->formData()['submit_label'];
            $this->submitLabel = $this->translator()->translation($label);
        }

        return parent::submitLabel();
    }

    /**
     * @return string
     */
    public function defaultFormTabsTemplate()
    {
        return 'charcoal/admin/template/form/nav-pills';
    }
}
