<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

// From Mustache
use Mustache_LambdaHelper as LambdaHelper;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextareaInput;

/**
 * TinyMCE Rich-Text Input Property
 */
class TinymceInput extends TextareaInput
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
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $dialogTitle;

    /**
     * Flag wether the "file picker" popup button should be displaed.
     *
     * @var boolean
     */
    private $showFilePicker;

    /**
     * URL for the "file picker" popup.
     *
     * @var string
     */
    private $filePickerUrl;

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
     * Retrieve the editor's {@see self::editorOptions() options} as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function editorOptionsAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options = ($options | JSON_PRETTY_PRINT);
        }

        return json_encode($this->editorOptions(), $options);
    }

    /**
     * Retrieve the editor's {@see self::editorOptions() options} as a JSON string, protected from Mustache.
     *
     * @return string Returns a stringified JSON object, protected from Mustache rendering.
     */
    public function escapedEditorOptionsAsJson()
    {
        return '{{=<% %>=}}'.$this->editorOptionsAsJson().'<%={{ }}=%>';
    }

    /**
     * Set the title for the file picker dialog.
     *
     * @param  mixed $title The dialog title.
     * @return self
     */
    public function setDialogTitle($title)
    {
        $this->dialogTitle = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Retrieve the default title for the file picker dialog.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    protected function defaultDialogTitle()
    {
        return $this->translator()->translation('Media Library');
    }

    /**
     * Retrieve the title for the file picker dialog.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function dialogTitle()
    {
        if ($this->dialogTitle === null) {
            $this->setDialogTitle($this->defaultDialogTitle());
        }

        return $this->dialogTitle;
    }

    /**
     * @param boolean $show The show file picker flag.
     * @return FileInput Chainable
     */
    public function setShowFilePicker($show)
    {
        $this->showFilePicker = !!$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showFilePicker()
    {
        if ($this->showFilePicker === null) {
            return $this->hasFilePicker();
        }

        return $this->showFilePicker;
    }

    /**
     * @return boolean
     */
    public function hasFilePicker()
    {
        return class_exists('\\elFinder');
    }

    /**
     * @param  string $url The file picker AJAX URL.
     * @return FileInput Chainable
     */
    public function setFilePickerUrl($url)
    {
        $this->filePickerUrl = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function filePickerUrl()
    {
        if (!$this->showFilePicker()) {
            return null;
        }

        return $this->filePickerUrl;
    }

    /**
     * Render the file picker URL with the correct object model context.
     *
     * This method (a necessary evil) allows one to customize the URL
     * without duplicating the template view.
     *
     * @see \Charcoal\Admin\Property\Input\FileInput::prepareFilePickerUrl()
     *
     * @return callable|null
     */
    public function prepareFilePickerUrl()
    {
        if (!$this->showFilePicker()) {
            return null;
        }

        $uri = $this->getFilePickerUrlTemplate();

        return function ($noop, LambdaHelper $helper) use ($uri) {
            $uri = $helper->render($uri);
            $this->setFilePickerUrl($uri);

            return null;
        };
    }

    /**
     * Retrieve the elFinder connector URL template for rendering.
     *
     * @return string
     */
    protected function getFilePickerUrlTemplate()
    {
        $uri = 'obj_type={{ objType }}&obj_id={{ objId }}&property={{ p.ident }}&callback={{ inputId }}';
        $uri = '{{# withAdminUrl }}elfinder?'.$uri.'{{/ withAdminUrl }}';

        return $uri;
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        return [
            'editor_options' => $this->editorOptions(),
            'dialog_title'   => (string)$this->dialogTitle(),
            'elfinder_url'   => $this->filePickerUrl(),
        ];
    }
}
