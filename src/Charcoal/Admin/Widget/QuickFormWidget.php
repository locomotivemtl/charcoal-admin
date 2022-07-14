<?php

namespace Charcoal\Admin\Widget;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\ObjectFormWidget;

// From 'charcoal-property'
use Charcoal\Property\ModelStructureProperty;

// From 'charcoal-ui'
use Charcoal\Admin\Ui\HasLanguageSwitcherInterface;
use Charcoal\Admin\Ui\HasLanguageSwitcherTrait;

/**
 * The quick form widget for editing objects on the go.
 */
class QuickFormWidget extends ObjectFormWidget implements
    HasLanguageSwitcherInterface
{
    use HasLanguageSwitcherTrait;

    /**
     * Ident for tab display.
     *
     * @const string
     */
    const DISPLAY_MODE_TAB = 'tab';

    /**
     * Ident for lang tab display.
     *
     * @const string
     */
    const DISPLAY_MODE_LANG = 'lang';

    /**
     * @param  array $data The widget data.
     * @return self
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if ($this->groupDisplayMode() === self::DISPLAY_MODE_LANG) {
            $this->setTabsTemplate('charcoal/admin/template/form/nav-tabs-languages');
        }

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

        if (isset($metadata['admin']['default_quick_form'])) {
            return $metadata['admin']['default_quick_form'];
        }

        if (isset($this->formData()['form_ident'])) {
            $ident = $this->formData()['form_ident'];

            if (is_string($ident) && !empty($ident)) {
                return $ident;
            }
        }

        return 'quick';
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        return array_merge_recursive(
            parent::widgetDataForJs(),
            [
                'is_display_mode_lang' => $this->isDisplayModeLang(),
                'show_language_switch' => $this->showLanguageSwitch(),
            ]
        );
    }

    /**
     * Retrieve the label for the form submission button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function submitLabel()
    {
        if (isset($this->formData()['submit_label'])) {
            $label = $this->formData()['submit_label'];
            $this->submitLabel = $this->translator()->translation($label);
        }

        return parent::submitLabel();
    }

    /**
     * @see    HasLanguageSwitcherTrait::showLanguageSwitch()
     * @return boolean
     */
    protected function resolveShowLanguageSwitch()
    {
        return $this->supportsLanguageSwitch();
    }

    /**
     * Determine if content groups are to be displayed as languages tabbable panes.
     *
     * @return boolean
     */
    public function isDisplayModeLang()
    {
        return ($this->groupDisplayMode() === self::DISPLAY_MODE_LANG);
    }

    /**
     * Determine if content groups are to be displayed as tabbable panes.
     *
     * @return boolean
     */
    public function isTabbable()
    {
        return in_array($this->groupDisplayMode(), $this->getTabbableDisplayModes());
    }

    /**
     * @return array
     */
    public function getTabbableDisplayModes()
    {
        return [
            self::DISPLAY_MODE_TAB,
            self::DISPLAY_MODE_LANG
        ];
    }

    /**
     * @return string
     */
    public function availableLanguagesAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options = ($options | JSON_PRETTY_PRINT);
        }

        return json_encode($this->languages(), $options);
    }

    /**
     * @return string
     */
    public function defaultFormTabsTemplate()
    {
        return 'charcoal/admin/template/form/nav-pills';
    }
}
