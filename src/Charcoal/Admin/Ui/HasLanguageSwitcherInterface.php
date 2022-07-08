<?php

namespace Charcoal\Admin\Ui;

/**
 * Defines a language switcher handler.
 *
 * Classes that implement this interface are in charge
 * of displaying or hiding the switcher as well as
 * deciding of that state,
 */
interface HasLanguageSwitcherInterface
{
    /**
     * Whether to display the language switcher.
     *
     * @return boolean
     */
    public function showLanguageSwitch();

    /**
     * Retrieve the available languages, formatted for the sidebar language-switcher.
     *
     * @see    FormGroupWidget::languages()
     * @return array
     */
    public function languages();
}
