<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * TinyMCE Rich-Text Input Property
 */
class TinymceInput extends AbstractPropertyInput
{
    /**
     * The TinyMCE editor settigns.
     *
     * @var array
     */
    private $editorOptions;

    /**
     * Label for the file picker dialog.
     *
     * @var TranslationString|string
     */
    private $dialogTitle;

    /**
     * Set the editor's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The editor options.
     * @return Tinymce Chainable
     */
    public function setEditorOptions(array $settings)
    {
        $this->editorOptions = array_merge($this->defaultEditorOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) editor options.
     *
     * @param  array $settings The editor options.
     * @return Tinymce Chainable
     */
    public function mergeEditorOptions(array $settings)
    {
        $this->editorOptions = array_merge($this->editorOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an editor option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return Tinymce Chainable
     */
    public function addEditorOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->editorOptions === null) {
            $this->editorOptions();
        }

        $this->editorOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the editor's options.
     *
     * @return array
     */
    public function editorOptions()
    {
        if ($this->editorOptions === null) {
            $this->selectOptions = $this->defaultSelectOptions();
        }

        return $this->editorOptions;
    }

    /**
     * Retrieve the default editor options.
     *
     * @return array
     */
    public function defaultEditorOptions()
    {
        $defaultData = $this->metadata()->defaultData();

        if (isset($defaultData['editor_options'])) {
            return $defaultData['editor_options'];
        }

        return [];
    }

    /**
     * Retrieve the editor's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function editorOptionsAsJson()
    {
        return json_encode($this->editorOptions());
    }

    /**
     * Set the title for the file picker dialog.
     *
     * @param  string|string[] $title The dialog title.
     * @return self
     */
    public function setDialogTitle($title)
    {
        if (TranslationString::isTranslatable($title)) {
            $this->dialogTitle = new TranslationString($title);
        } else {
            $this->dialogTitle = null;
        }

        return $this;
    }

    /**
     * Retrieve the default title for the file picker dialog.
     *
     * @return string[]
     */
    protected function defaultDialogTitle()
    {
        return [
            'en' => 'Media Library',
            'fr' => 'Bibliothèque de médias'
        ];
    }

    /**
     * Retrieve the title for the file picker dialog.
     *
     * @return TranslationString|string|null
     */
    public function dialogTitle()
    {
        if ($this->dialogTitle === null) {
            $this->setDialogTitle($this->defaultDialogTitle());
        }

        return $this->dialogTitle;
    }
}
