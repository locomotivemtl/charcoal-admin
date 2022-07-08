<?php

namespace Charcoal\Admin\Ui;

/**
 * Defines awareness of the language switcher.
 *
 * Classes that implement this interface can be used to determine
 * if the language switcher should be displayed or hidden,
 */
interface LanguageSwitcherAwareInterface
{
    /**
     * Whether a language switcher could be displayed.
     *
     * @return bool
     */
    public function supportsLanguageSwitch(): bool;
}
