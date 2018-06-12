<?php

namespace Charcoal\Admin\Property\Input\Tinymce;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TinymceInput;

/**
 * Basic Features for a TinyMCE Rich-Text Input Property
 */
class BasicInput extends TinymceInput
{
    /**
     * Return a new object property for sports.
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data = null)
    {
        parent::__construct($data);

        $defaultData = $this->metadata()->defaultData();
        if ($defaultData) {
            $this->setData($defaultData);
        }
    }
}
