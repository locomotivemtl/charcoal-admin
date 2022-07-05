<?php

namespace Charcoal\Admin\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface as BaseFormGroupInterface;

/**
 * Defines an admin form group.
 */
interface FormGroupInterface extends
    BaseFormGroupInterface
{
    /**
     * @return array
     */
    public function groupProperties();
}
