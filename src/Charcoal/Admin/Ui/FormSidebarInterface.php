<?php

namespace Charcoal\Admin\Ui;

// From 'charcoal-ui'
use Charcoal\Ui\PrioritizableInterface;

// From 'charcoal-admin'
use Charcoal\Ui\Form\FormInterface;

/**
 * Defines an admin form sidebar
 */
interface FormSidebarInterface extends
    PrioritizableInterface
{
    /**
     * Set the form widget the sidebar belongs to.
     *
     * @param FormInterface $form The related form widget.
     * @return FormSidebarInterface Chainable
     */
    public function setForm(FormInterface $form);

    /**
     * Retrieve the form widget the sidebar belongs to.
     *
     * @return FormInterface
     */
    public function form();

    /**
     * @param boolean $active The active flag.
     * @return FormSidebarInterface Chainable
     */
    public function setActive($active);

    /**
     * @return boolean
     */
    public function active();
}
