<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextareaInput;

/**
 * JSON Editor Input Property
 */
class JsonEditorInput extends TextareaInput
{
    /**
     * The JSONEditor settings
     *
     * @var array
     * @see https://github.com/josdejong/jsoneditor/blob/master/docs/api.md#configuration-options
     */
    private $editorOptions;

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
            $this->editorOptions = $this->defaultEditorOptions();
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
     * Retrieve the property's value as a json encoded string.
     *
     * @return string
     */
    public function jsonVal()
    {
        $json = $this->propertyVal();
        if (!is_string($json)) {
            $json = json_encode($json);
        }
        if (!$json || $json == 'null') {
            $json = '';
        }
        return $json;
    }
}
