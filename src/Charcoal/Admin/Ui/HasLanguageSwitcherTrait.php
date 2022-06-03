<?php

namespace Charcoal\Admin\Ui;

/**
 * Implements Charcoal\Admin\Ui\HasLanguageSwitcherInterface
 */
trait HasLanguageSwitcherTrait
{
    /**
     * Whether to display the language switcher.
     *
     * @var boolean|null
     */
    protected $showLanguageSwitch;

    /**
     * @return boolean
     */
    public function showLanguageSwitch()
    {
        if ($this->showLanguageSwitch === null) {
            $this->showLanguageSwitch = $this->resolveShowLanguageSwitch();
        }
        return $this->showLanguageSwitch;
    }

    /**
     * Retrieve the available languages, formatted for the sidebar language-switcher.
     *
     * @see    FormGroupWidget::languages()
     * @return array
     */
    public function languages()
    {
        $currentLocale = $this->translator()->getLocale();
        $locales = $this->translator()->locales();
        $languages = [];

        uasort($locales, [ 'Charcoal\Admin\Support\Sorter', 'sortByPriority' ]);

        foreach ($locales as $locale => $localeStruct) {
            /**
             * @see \Charcoal\Admin\Widget\FormGroupWidget::languages()
             * @see \Charcoal\Property\LangProperty::localeChoices()
             */
            if (isset($localeStruct['name'])) {
                $label = $this->translator()->translation($localeStruct['name']);
            } else {
                $trans = 'locale.'.$locale;
                if ($trans === $this->translator()->trans($trans)) {
                    $label = strtoupper($locale);
                } else {
                    $label = $this->translator()->translation($trans);
                }
            }

            $isCurrent = ($locale === $currentLocale);
            $languages[] = [
                'cssClasses' => ($isCurrent) ? 'btn-primary' : 'btn-outline-primary',
                'ident'      => $locale,
                'name'       => $label,
                'current'    => $isCurrent
            ];
        }

        return $languages;
    }
    /**
     * Resolves whether to display the language switcher or not.
     *
     * @var boolean
     */
    abstract protected function resolveShowLanguageSwitch();
}
