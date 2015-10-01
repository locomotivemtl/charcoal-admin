<?php

namespace Charcoal\Admin\Widget;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Module `charcoal-core` dependencies
use \Charcoal\Translation\TranslationString;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Widget\FormWidget;
use \Charcoal\Admin\Widget\LayoutWidget;

use \Charcoal\Admin\Ui\FormGroupInterface;
use \Charcoal\Admin\Ui\FormGroupTrait;

/**
* Form Group Widget Controller
*/
class FormGroupWidget extends AdminWidget implements FormGroupInterface
{
    use FormGroupTrait;

    /**
    * @var LayoutWidget $_layout
    */
    public $_layout;


    /**
    * @var array $_group_properties
    */
    private $_group_properties = [];

    /**
    * @var TranslationString $_description
    */
    private $_description;
    /**
    * @var TranslationString $_notes
    */
    private $_notes;

    /**
    * If it is set to false, will disable display of title.
    * @var boolean
    */
    private $_show_title = true;
    /**
    * If it is set to false, will disable display of description
    * @var boolean
    */
    private $_show_description = true;
    /**
    * If it is set to false, will disable display of the notes (footer).
    * @var boolean
    */
    private $_show_notes = true;

    /**
    * @var boolean
    */
    private $_show_header = true;
    /**
    * @var boolean
    */
    private $_show_footer = true;



    /**
    * @var string
    * @return FormGroup Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['properties']) && $data['properties'] !== null) {
            $this->set_group_properties($data['properties']);
        }

        return $this;
    }

    /**
    * @return string
    */
    public function widget_type()
    {
        return 'charcoal/admin/widget/formgroup';
    }

    /**
    * Sets the widget options
    */
    public function set_widget_options($opts)
    {
        if (!$opts) {
            return $this;
        }
        $this->_widget_options = $opts;

        return $this;
    }

    public function widget_options()
    {
        return $this->_widget_options;
    }

    public function json_widget_options()
    {
        if (!$this->widget_options()) {
            return false;
        }

        return json_encode($this->widget_options());
    }

    /**
    * @param LayoutWidget|array
    * @throws InvalidArgumentException
    * @return FormGroupWidget Chainable
    */
    public function set_layout($layout)
    {
        if (($layout instanceof LayoutWidget)) {
            $this->_layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget();
            $l->set_data($layout);
            $this->_layout = $l;
        } else {
            throw new InvalidArgumentException('LayoutWidget must be a LayoutWidget object or an array');
        }
        return $this;
    }

    /**
    * @return LayoutWidget
    */
    public function layout()
    {
        return $this->_layout;
    }





    public function set_group_properties($properties)
    {
        $this->_group_properties = $properties;
        return $this;
    }

    public function group_properties()
    {
        return $this->_group_properties;

    }

    public function form_properties()
    {
        $group_properties = $this->group_properties();
        $form_properties = $this->form()->form_properties($group_properties);

        $ret = [];
        foreach ($form_properties as $property_ident => $property) {
            if (in_array($property_ident, $group_properties)) {
                //var_dump($property);
                if (is_callable([$this->form(), 'obj'])) {
                    $val = $this->form()->obj()->p($property_ident)->val();
                    $property->set_property_val($val);
                }
                yield $property_ident => $property;
            }
        }
    }

    /**
    * @var integer $priority
    * @throws InvalidArgumentException
    * @return FormGroupWidget Chainable
    */
    public function set_priority($priority)
    {
        if (!is_int($priority)) {
            throw new InvalidArgumentException('Priority must be an integer');
        }
        $priority = (int)$priority;
        $this->_priority = $priority;
        return $this;
    }

    /**
    * @return integer
    */
    public function priority()
    {
        return $this->_priority;
    }


    public function set_title($title)
    {
        $this->_title = new TranslationString($title);
        return $this;
    }

    public function title()
    {
        return $this->_title;
    }

    /**
    * @var mixed $description
    * @return $
    */
    public function set_description($description)
    {
        $this->_description = new TranslationString($description);
        return $this;
    }

    /**
    * @return TranslationString
    */
    public function description()
    {
        return new TranslationString('Group Description');
    }

    /**
    * @param mixed $notes
    * @return FormGroupWidget Chainable
    */
    public function set_notes($notes)
    {
        $this->_notes = new TranslationString($notes);
        return $this;
    }

    /**
    * @return TranslationString
    */
    public function notes()
    {
        return new TranslationString('Group Notes');
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
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
    * @return FormGroup Chainable
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
        return true;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
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
            $notes = $this->notes();
            return !!$notes;
        }
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_show_header($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_header = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_header()
    {
        return true;
    }

    /**
    * @param boolean $show
    * @throws InvalidArgumentException
    * @return FormGroup Chainable
    */
    public function set_show_footer($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show must be a boolean');
        }
        $this->_show_fooger = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_footer()
    {
        if ($this->_show_footer === false) {
            return false;
        } else {
            return $this->show_notes();
        }
    }


}
