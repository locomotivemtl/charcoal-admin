<?php

namespace Charcoal\Admin\Template;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// Local Dependency
use \Charcoal\Admin\AdminTemplate as AdminTemplate;

/**
 * Admin Error Handler Template
 */
class HandlerTemplate extends AdminTemplate
{
    /**
     * The current error title.
     *
     * @var TranslationString
     */
    private $errorTitle;

    /**
     * The current error message.
     *
     * @var TranslationString
     */
    private $errorMessage;

    /**
     * @return string
     */
    public function ident()
    {
        return 'error';
    }

    /**
     * Set the handler's error message.
     *
     * @param  mixed $message
     * @return self
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = new TranslationString($message);

        return $this;
    }

    /**
     * Retrieve the error message.
     *
     * @return TranslationString
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Set the handler's error title.
     *
     * @param  mixed $title
     * @return self
     */
    public function setErrorTitle($title)
    {
        $this->errorTitle = new TranslationString($title);

        return $this;
    }

    /**
     * Retrieve the document title.
     *
     * @return TranslationString
     */
    public function errorTitle()
    {
        return $this->errorTitle;
    }

    /**
     * Retrieve the document title.
     *
     * @return TranslationString
     */
    public function title()
    {
        $title = parent::title();

        if (!isset($title)) {
            $title = $this->errorTitle();
        }

        return $title;
    }

    /**
     * Error handler response is available to all users, no login required.
     *
     * @return boolean
     */
    protected function authRequired()
    {
        return false;
    }
}
