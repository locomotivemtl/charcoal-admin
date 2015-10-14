<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

use \Charcoal\Admin\AdminWidget;

class TextWidget extends AdminWidget
{

    /**
    * @var string $title
    */
    protected $title = '';
    /**
    * @var string $subtitle
    */
    protected $subtitle = '';
    /**
    * @var string $description
    */
    protected $description = '';
    /**
    * @var string $notes
    */
    protected $notes = '';

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return Text Chainable
    */
    public function set_show_title($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException(
                'Show must be a boolean'
            );
        }
        $this->show_title = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_title()
    {
        if ($this->show_title === false) {
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
            throw new InvalidArgumentException(
                'Show must be a boolean'
            );
        }
        $this->show_subtitle = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_subtitle()
    {
        if ($this->show_subtitle === false) {
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
            throw new InvalidArgumentException(
                'Show must be a boolean'
            );
        }
        $this->show_description = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_description()
    {
        if ($this->show_description === false) {
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
            throw new InvalidArgumentException(
                'Show must be a boolean'
            );
        }
        $this->show_notes = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_notes()
    {
        if ($this->show_notes === false) {
            return false;
        } else {
            return !!$this->notes();
        }
    }

    public function set_title($title)
    {
        $this->title = $title;
        return $this;
    }

    public function title()
    {
        return $this->title;
    }

    public function set_subtitle($subtitle)
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    public function subtitle()
    {
        return $this->subtitle;
    }

    public function set_description($description)
    {
        $this->description = $description;
        return $this;
    }

    public function description()
    {
        return $this->description;
    }

    public function set_notes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    public function notes()
    {
        return $this->notes;
    }
}
