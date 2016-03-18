<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 *
 */
class TinymceInput extends AbstractPropertyInput
{
    /**
     * The TinyMCE options
     * @var array $editorOptions
     */
    private $editorOptions = [];

    /**
     * @param array $opts The editor options.
     * @return Tinymce Chainable
     */
    public function setEditorOptions(array $opts)
    {
        $this->editorOptions = $opts;
        return $this;
    }

    /**
     * @return array
     */
    public function editorOptions()
    {
        return $this->editorOptions;
    }

    /**
     * Get the editor options as a JSON string
     * @return string
     */
    public function editorOptionsJson()
    {
        return json_encode($this->editorOptions());
    }

    /**
     * @param string $optIdent The option ident.
     * @param array  $optVal   The option value.
     * @throws InvalidArgumentException If the ident is not a string.
     * @return Tinymce Chainable
     */
    public function addEditorOption($optIdent, array $optVal)
    {
        if (!is_string($optIdent)) {
            throw new InvalidArgumentException(
                'Option ident must be a string.'
            );
        }
        $this->editorOptions[$optIdent] = $optVal;
        return $this;
    }
}
