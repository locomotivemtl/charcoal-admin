<?php

namespace Charcoal\Admin\Template;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 * Admin Error Handler Template
 */
class HandlerTemplate extends AdminTemplate
{
    /**
     * The current error title.
     *
     * @var Translation|string|null
     */
    private $errorTitle;

    /**
     * The current error message.
     *
     * @var Translation|string|null
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
     * @param  mixed $message The error message.
     * @return self
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $this->translator()->translation($message);

        return $this;
    }

    /**
     * Retrieve the error message.
     *
     * @return Translation|string|null
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Set the handler's error title.
     *
     * @param  mixed $title The error title.
     * @return self
     */
    public function setErrorTitle($title)
    {
        $this->errorTitle = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Retrieve the error title.
     *
     * @return Translation|string|null
     */
    public function errorTitle()
    {
        return $this->errorTitle;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return Translation|string|null
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
