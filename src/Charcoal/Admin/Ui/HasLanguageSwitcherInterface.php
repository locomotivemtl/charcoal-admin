<?php

namespace Charcoal\Admin\Ui;

/**
 * Language switcher awareness
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
