<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * File Property Input
 */
class FileInput extends AbstractPropertyInput
{
    /**
     * Flag wether the "file preview" should be displayed.
     *
     * @var bool $showFilePreview
     */
    private $showFilePreview = true;

    /**
     * Flag wether the "file upload" input should be displayed.
     *
     * @var bool $showFileUpload
     */
    private $showFileUpload = true;

    /**
     * Flag wether the "file picker" popup button should be displaed.
     *
     * @var bool $showFilePicker
     */
    private $showFilePicker = true;

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
        return $this->showFilePicker;
    }
}
