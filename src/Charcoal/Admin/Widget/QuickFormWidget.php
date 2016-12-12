<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Admin\Widget\ObjectFormWidget;

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

        return 'quick';
    }
}
