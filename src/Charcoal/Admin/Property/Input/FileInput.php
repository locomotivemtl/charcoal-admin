<?php

namespace Charcoal\Admin\Property\Input;

// From Pimple
use Pimple\Container;

// From Mustache
use Mustache_LambdaHelper as LambdaHelper;

// // From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * File Property Input
 */
class FileInput extends AbstractPropertyInput
{
    /**
     * The base URI for the Charcoal application.
     *
     * @var \Psr\Http\Message\UriInterface|string
     */
    public $baseUrl;

    /**
     * A string of accepted file types.
     *
     * @var string
     */
    private $accept;

    /**
     * Flag wether the "file preview" should be displayed.
     *
     * @var boolean
     */
    private $showFilePreview = true;

    /**
     * Flag wether the "file upload" input should be displayed.
     *
     * @var boolean
     */
    private $showFileUpload;

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
     * Label for the file picker dialog.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $dialogTitle;

    /**
     * Label for the "file picker" button.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $chooseButtonLabel;

    /**
     * Label for the "remove file" button.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $removeButtonLabel;

    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'file';
    }

    /**
     * Set a list of unique file type specifiers.
     *
     * @link   https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/file
     * @param  string|string[] $types The accepted MIME types.
     * @return self
     */
    public function setAccept($types)
    {
        if (is_array($types)) {
            $types = implode(',', $types);
        }

        $this->accept = $types;
        return $this;
    }

    /**
     * Retrieving a comma-separated list of unique file type specifiers.
     *
     * @return string
     */
    public function accept()
    {
        if ($this->accept === null) {
            $types = $this->property()['acceptedMimetypes'];
            return implode(',', $types);
        }

        return $this->accept;
    }

    /**
     * @return string|null
     */
    public function abridgedInputVal()
    {
        $val = (string)$this->inputVal();
        $val = preg_replace('!^'.preg_quote($this->p()['uploadPath'], '!').'!', '', $val);

        if (strpos($val, '://') !== false) {
            $host = parse_url($val, PHP_URL_HOST);
            $path = ltrim(substr($val, (strpos($val, $host) + strlen($host) + 1)), '/');
            if (mb_strlen($path) > 30) {
                $a = 12;
                $z = 12;
                $abr = (($a > 0) ? mb_substr($path, 0, $a) : '').'&hellip;'.(($z > 0) ? mb_substr($path, -$z) : '');
                $val = str_replace($path, $abr, $val);
            }
        }

        return $val;
    }

    /**
     * @return string|null
     */
    public function filePreview()
    {
        $value = $this->inputVal();
        if ($value) {
            return $this->view()->render('charcoal/admin/property/input/file/preview', $this);
        }

        return '';
    }

    /**
     * Retrieve input value for file preview.
     *
     * @return string
     */
    public function placeholderVal()
    {
        $val = parent::placeholder();
        if (empty($val)) {
            return '';
        }

        $parts = parse_url($val);
        if (empty($parts['scheme']) && !in_array($val[0], [ '/', '#', '?' ])) {
            $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
            $query = isset($parts['query']) ? $parts['query'] : '';
            $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';
            $val   = $this->baseUrl->withPath($path)->withQuery($query)->withFragment($hash);
        }

        return $val;
    }

    /**
     * Retrieve input value for file preview.
     *
     * @return string
     */
    public function previewVal()
    {
        $val = parent::inputVal();
        if (empty($val)) {
            return '';
        }

        $parts = parse_url($val);
        if (empty($parts['scheme']) && !in_array($val[0], [ '/', '#', '?' ])) {
            $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
            $query = isset($parts['query']) ? $parts['query'] : '';
            $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';
            $val   = $this->baseUrl->withPath($path)->withQuery($query)->withFragment($hash);
        }

        return $val;
    }

    /**
     * @param boolean $show The show file preview flag.
     * @return FileInput Chainable
     */
    public function setShowFilePreview($show)
    {
        $this->showFilePreview = !!$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showFilePreview()
    {
        return $this->showFilePreview;
    }

    /**
     * @param boolean $show The show file upload flag.
     * @return FileInput Chainable
     */
    public function setShowFileUpload($show)
    {
        $this->showFileUpload = !!$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showFileUpload()
    {
        if ($this->showFileUpload === null) {
            return !($this->showFilePicker === true && $this->hasFilePicker());
        }

        return $this->showFileUpload;
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
            return !($this->showFileUpload === true);
        }

        return $this->showFilePicker && $this->hasFilePicker();
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
     * Necessary evil to render the file picker URL
     * with the correct object model context.
     *
     * @see \Charcoal\Admin\Property\Input\TinymceInput::prepareFilePickerUrl()
     *
     * @return callable|null
     */
    public function prepareFilePickerUrl()
    {
        if (!$this->showFilePicker()) {
            return null;
        }

        if ($this->filePickerUrl !== null) {
            // return null;
        }

        $uri = 'obj_type={{ objType }}&obj_id={{ objId }}&property={{ p.ident }}&callback={{ inputId }}';
        $uri = '{{# withAdminUrl }}elfinder?'.$uri.'{{/ withAdminUrl }}';

        return function ($noop, LambdaHelper $helper) use ($uri) {
            $uri = $helper->render($uri);
            $this->filePickerUrl = $uri;

            return null;
        };
    }

    /**
     * Set the title for the file picker dialog.
     *
     * @param  string|string[] $title The dialog title.
     * @return self
     */
    public function setDialogTitle($title)
    {
        $this->dialogTitle = $this->translator()->translation($title);

        return $this;
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
     * Set the label for the file picker button.
     *
     * @param  string|string[] $label The button label.
     * @return self
     */
    public function setChooseButtonLabel($label)
    {
        $this->chooseButtonLabel = $this->translator()->translation($label);

        return $this;
    }

    /**
     * Retrieve the label for the file picker button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function chooseButtonLabel()
    {
        if ($this->chooseButtonLabel === null) {
            $this->setChooseButtonLabel($this->defaultChooseButtonLabel());
        }

        return $this->chooseButtonLabel;
    }

    /**
     * Set the label for the file removal button.
     *
     * @param  string|string[] $label The button label.
     * @return self
     */
    public function setRemoveButtonLabel($label)
    {
        $this->removeButtonLabel = $this->translator()->translation($label);

        return $this;
    }

    /**
     * Retrieve the label for the file removal button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function removeButtonLabel()
    {
        if ($this->removeButtonLabel === null) {
            $this->setRemoveButtonLabel($this->defaultRemoveButtonLabel());
        }

        return $this->removeButtonLabel;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->baseUrl = $container['base-url'];
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
     * Retrieve the default label for the file picker button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    protected function defaultChooseButtonLabel()
    {
        return $this->translator()->translation('Choose File…');
    }

    /**
     * Retrieve the default label for the file removal button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    protected function defaultRemoveButtonLabel()
    {
        return $this->translator()->translation('Remove File');
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        return [
            'input_name'   => $this->inputName(),
            # 'input_val'    => $this->inputVal(),
            'dialog_title' => (string)$this->dialogTitle(),
            'elfinder_url' => $this->filePickerUrl(),
        ];
    }
}
