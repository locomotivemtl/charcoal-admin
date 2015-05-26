<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Admin\Widget as Widget;

class Table extends Widget
{
    private $_properties;
    private $_properties_options;

    private $_orders;
    private $_filters;

    public function __construct($data = null)
    {
        //parent::__construct($data);

        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * @var array $data
    * @throws InvalidArgumentException
    * @return Form (Chainable)
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }

        parent::set_data($data);

        if (isset($data['layout']) && $data['layout'] !== null) {
            $this->set_layout($data['layout']);
        }

        return $this;
    }

    public function set_layout($layout)
    {
        if (($layout instanceof Layout)) {
            $this->_layout = $layout;
        } else if (is_array($layout)) {
            $layout = new Layout();
            $layout->set_data($layout);
            $this->_layout = $layout;
        } else {
            throw new InvalidArgumentException('Layout must be a Layout object or an array');
        }
    }

    public function layout()
    {
        return $this->_layout;
    }

}
