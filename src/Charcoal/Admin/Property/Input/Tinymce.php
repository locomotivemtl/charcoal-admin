<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\Input\Textarea as TextareaInput;

/**
* Tinymce Property Input
*/
class Tinymce extends TextareaInput
{
    /**
    * The TinyMCE options
    * @var array $_editor_options
    */
    private $_editor_options = [];

    /**
    * @param array $data
    * @return Tinymce Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);
        if(isset($data['editor_options']) && $data['editor_options'] !== null) {
            $this->set_editor_options($data['editor_options']);
        }
        return $this;
    }

    /**
    * @param array $opts
    * @return Tinymce Chainable
    */
    public function set_editor_options(array $opts)
    {
        $this->_editor_options = $opts;
        return $this;
    }

    /**
    * @return array
    */
    public function editor_options()
    {
        return $this->_editor_options;
    }

    /**
    * Get the editor options as a JSON string
    * @return string
    */
    public function editor_options_json()
    {
        return json_encode($this->editor_options());
    }

    /**
    * @param string $opt_ident
    * @param array $opt_val
    * @throws InvalidArgumentException
    * @return Tinymce Chainable
    */
    public function add_editor_option($opt_ident, $opt_val)
    {
        if(!is_string($opt_ident)) {
            throw new InvalidArgumentException('Option ident must be a string.');
        }
        $this->_editor_options[$opt_ident] = $opt_val;
        return $this;
    }

}
