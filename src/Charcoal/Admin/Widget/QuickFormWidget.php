<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Admin\Widget\ObjectFormWidget;

/**
 * The quick form widget for editing objects on the go.
 */
class QuickFormWidget extends ObjectFormWidget
{
    /**
     * @param array|ArrayInterface $data The widget data.
     * @return QuickFormWidget Chainable
     */
    public function setData($data)
    {
        $data = array_merge($_GET, $data);
        $data = array_merge($_POST, $data);

        parent::setData($data);

        return $this;
    }

    /**
     * Retrieve the identifier of the form to use, or its fallback.
     *
     * @see    ObjectFormWidget::formIdentFallback()
     * @return string
     */
    public function formIdentFallback()
    {
        $metadata = $this->obj()->metadata();

        if (isset($metadata['admin'])) {
            $metadata = $metadata['admin'];

            if (isset($metadata['default_quick_form'])) {
                return $metadata['default_quick_form'];
            }
        }

        return 'quick';
    }
}
