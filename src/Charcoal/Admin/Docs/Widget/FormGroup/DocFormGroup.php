<?php

namespace Charcoal\Admin\Docs\Widget\FormGroup;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormGroupWidget;

/**
 *
 */
class DocFormGroup extends FormGroupWidget
{
    /**
     * @return string
     */
    public function type()
    {
        return 'charcoal/admin/docs/widget/form-group-widget';
    }

    /**
     * @return boolean
     */
    public function hidden()
    {
        if ($this->description() || $this->notes() || count($this->groupProperties())) {
            return false;
        }

        return true;
    }
}
