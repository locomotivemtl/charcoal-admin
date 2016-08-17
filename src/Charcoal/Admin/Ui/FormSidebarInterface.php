<?php

namespace Charcoal\Admin\Ui;

use \Charcoal\Ui\Form\FormInterface;

/**
 * Form Sidebar Interface
 */
interface FormSidebarInterface
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

    /**
     * Set the widget's priority or sorting index.
     *
     * @param integer $priority An index, for sorting.
     * @return FormSidebarInterface Chainable
     */
    public function setPriority($priority);

    /**
     * Retrieve the widget's priority or sorting index.
     *
     * @return integer
     */
    public function priority();
}
