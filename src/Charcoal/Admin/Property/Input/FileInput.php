<?php

namespace Charcoal\Admin\Property\Input;

use \Pimple\Container;

// Local Dependency
use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * File Property Input
 */
class FileInput extends AbstractPropertyInput
{
    /**
     * The base URI for the Charcoal application.
     *
     * @var string|\Psr\Http\Message\UriInterface
     */
    public $baseUrl;

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
     * Label for the file picker dialog.
     *
     * @var \Charcoal\Translator\Translation
     */
    private $dialogTitle;

    /**
     * Label for the "file picker" button.
     *
     * @var \Charcoal\Translator\Translation
     */
    private $chooseButtonLabel;

    /**
     * Label for the "remove file" button.
     *
     * @var \Charcoal\Translator\Translation
     */
    private $removeButtonLabel;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->baseUrl = $container['base-url'];
    }

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
     * @return string|null
     */
    public function abridgedInputVal()
    {
        $val = (string)$this->inputVal();
        $val = preg_replace('!^'.preg_quote($this->p()->uploadPath(), '!').'!', '', $val);

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
    public function previewVal()
    {
        $val = parent::inputVal();

        if (!parse_url($val, PHP_URL_SCHEME)) {
            $uri = $this->baseUrl;
            return $uri->withPath($val);
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
            return !($this->showFilePicker === true);
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

        return $this->showFilePicker;
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
     * @return \Charcoal\Translator\Translation
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
     * Retrieve the default label for the file picker button.
     *
     * @return string[]
     */
    protected function defaultChooseButtonLabel()
    {
        return [
            'en' => 'Choose File…',
            'fr' => 'Choisissez un fichier…'
        ];
    }

    /**
     * Retrieve the label for the file picker button.
     *
     * @return \Charcoal\Translator\Translation
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
     * Retrieve the default label for the file removal button.
     *
     * @return string[]
     */
    protected function defaultRemoveButtonLabel()
    {
        return [
            'en' => 'Remove File',
            'fr' => 'Retirer le fichier'
        ];
    }

    /**
     * Retrieve the label for the file removal button.
     *
     * @return \Charcoal\Translator\Translation
     */
    public function removeButtonLabel()
    {
        if ($this->removeButtonLabel === null) {
            $this->setRemoveButtonLabel($this->defaultRemoveButtonLabel());
        }

        return $this->removeButtonLabel;
    }
}
