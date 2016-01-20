<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

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
     * @param array $opts
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
     * @param string $opt_ident
     * @param array  $opt_val
     * @throws InvalidArgumentException
     * @return Tinymce Chainable
     */
    public function addEditorOption($opt_ident, $opt_val)
    {
        if (!is_string($opt_ident)) {
            throw new InvalidArgumentException(
                'Option ident must be a string.'
            );
        }
        $this->editorOptions[$opt_ident] = $opt_val;
        return $this;
    }
}
