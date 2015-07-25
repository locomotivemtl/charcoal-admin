<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\AdminWidget as AdminWidget;

class TextWidget extends AdminWidget
{

    /**
    * @var string $_title
    */
    protected $_title = '';
    /**
    * @var string $_subtitle
    */
    protected $_subtitle = '';
    /**
    * @var string $_description
    */
    protected $_description = '';
    /**
    * @var string $_notes
    */
    protected $_notes = '';

    /**
    * @param array $data
    * @return Text Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['show_title']) && $data['show_title'] !== null) {
            $this->set_show_title($data['show_title']);
        }
        if (isset($data['show_subtitle']) && $data['show_subtitle'] !== null) {
            $this->set_show_subtitle($data['show_subtitle']);
        }
        if (isset($data['show_description']) && $data['show_description'] !== null) {
            $this->set_show_description($data['show_description']);
        }
        if (isset($data['show_notes']) && $data['show_notes'] !== null) {
            $this->set_show_notes($data['show_notes']);
        }
        if (isset($data['title']) && $data['title'] !== null) {
            $this->set_title($data['title']);
        }
        if (isset($data['subtitle']) && $data['subtitle'] !== null) {
            $this->set_subtitle($data['subtitle']);
        }
        if (isset($data['description']) && $data['description'] !== null) {
            $this->set_description($data['description']);
        }
        if (isset($data['notes']) && $data['notes'] !== null) {
            $this->set_notes($data['notes']);
        }

        return $this;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_show_title($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_title = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_title()
    {
        if ($this->_show_title === false) {
            return false;
        } else {
            return !!$this->title();
        }
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_show_subtitle($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_subtitle = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_subtitle()
    {
        if ($this->_show_subtitle === false) {
            return false;
        } else {
            return !!$this->subtitle();
        }
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_show_description($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_description = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_description()
    {
        if ($this->_show_description === false) {
            return false;
        } else {
            return !!$this->description();
        }
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_show_notes($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_notes = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_notes()
    {
        if ($this->_show_notes === false) {
            return false;
        } else {
            return !!$this->notes();
        }
    }

    public function set_title($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function title()
    {
        return $this->_title;
    }

    public function set_subtitle($subtitle)
    {
        $this->_subtitle = $subtitle;
        return $this;
    }

    public function subtitle()
    {
        return $this->_subtitle;
    }

    public function set_description($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function description()
    {
        return $this->_description;
    }

    public function set_notes($notes)
    {
        $this->_notes = $notes;
        return $this;
    }

    public function notes()
    {
        return $this->_notes;
    }
}
