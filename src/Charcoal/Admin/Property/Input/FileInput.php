<?php

namespace Charcoal\Admin\Property\Input;

use \Pimple\Container;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

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
     * @var TranslationString|string
     */
    private $dialogTitle;

    /**
     * Label for the "file picker" button.
     *
     * @var TranslationString|string
     */
    private $chooseButtonLabel;

    /**
     * Label for the "remove file" button.
     *
     * @var TranslationString|string
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
     * @return string|null
     */
    public function filePreview()
    {
        return null;
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

    /**
     * Set the label for the file picker button.
     *
     * @param  string|string[] $label The button label.
     * @return self
     */
    public function setChooseButtonLabel($label)
    {
        if (TranslationString::isTranslatable($label)) {
            $this->chooseButtonLabel = new TranslationString($label);
        } else {
            $this->chooseButtonLabel = null;
        }

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
     * @return TranslationString|string|null
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
        if (TranslationString::isTranslatable($label)) {
            $this->removeButtonLabel = new TranslationString($label);
        } else {
            $this->removeButtonLabel = null;
        }

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
     * @return TranslationString|string|null
     */
    public function removeButtonLabel()
    {
        if ($this->removeButtonLabel === null) {
            $this->setRemoveButtonLabel($this->defaultRemoveButtonLabel());
        }

        return $this->removeButtonLabel;
    }
}
