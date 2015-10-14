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
    * @var LayoutWidget $layout
    */
    public $layout;


    /**
    * @var array $group_properties
    */
    private $group_properties = [];

    /**
    * @var TranslationString $description
    */
    private $description;
    /**
    * @var TranslationString $notes
    */
    private $notes;

    /**
    * If it is set to false, will disable display of title.
    * @var boolean $show_title
    */
    private $show_title = true;
    /**
    * If it is set to false, will disable display of description
    * @var boolean $show_description
    */
    private $show_description = true;
    /**
    * If it is set to false, will disable display of the notes (footer).
    * @var boolean $show_notes
    */
    private $show_notes = true;

    /**
    * @var boolean $show_header
    */
    private $show_header = true;
    /**
    * @var boolean $show_footer
    */
    private $show_footer = true;

    /**
    * @var string
    * @return FormGroupWidget Chainable
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
        $this->widget_options = $opts;

        return $this;
    }

    public function widget_options()
    {
        return $this->widget_options;
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
            $this->layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget([
                'logger'=>$this->logger()
            ]);
            $l->set_data($layout);
            $this->layout = $l;
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
        return $this->layout;
    }





    public function set_group_properties($properties)
    {
        $this->group_properties = $properties;
        return $this;
    }

    public function group_properties()
    {
        return $this->group_properties;

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


    public function set_title($title)
    {
        $this->title = new TranslationString($title);
        return $this;
    }

    public function title()
    {
        return $this->title;
    }

    /**
    * @var mixed $description
    * @return $
    */
    public function set_description($description)
    {
        $this->description = new TranslationString($description);
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
        $this->notes = new TranslationString($notes);
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
    * @return FormGroup Chainable
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
            throw new InvalidArgumentException(
                'Show must be a boolean'
            );
        }
        $this->show_header = $show;
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
            throw new InvalidArgumentException(
                'Show must be a boolean'
            );
        }
        $this->show_fooger = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_footer()
    {
        if ($this->show_footer === false) {
            return false;
        } else {
            return $this->show_notes();
        }
    }

}
