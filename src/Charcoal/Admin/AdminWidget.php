<?php

namespace Charcoal\Admin;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Translation\TranslationString as TranslationString;

// From `charcoal-base`
use \Charcoal\App\Template\AbstractWidget as AbstractWidget;
use \Charcoal\Widget\WidgetView as WidgetView;

/**
* The base Widget for the `admin` module.
*/
class AdminWidget extends AbstractWidget
{
    /**
    * @var string $widget_id
    */
    public $widget_id;

    /**
    * @var string $type
    */
    private $type;
    /**
    * @var string $ident
    */
    private $ident = '';
    /**
    * @var mixed $label
    */
    private $label;
    /**
    * @var string $lang
    */
    private $lang;
    /**
    * @var bool $show_label
    */
    private $show_label;
    /**
    * @var bool $show_actions
    */
    private $show_actions;


    public function set_widget_id($widget_id)
    {
        $this->widget_id = $widget_id;
        return $this;
    }

    public function widget_id()
    {
        if (!$this->widget_id) {
            $this->widget_id = 'widget_'.uniqid();
        }
        return $this->widget_id;
    }

    /**
    * @param string $type
    * @throws InvalidArgumentException
    * @return AdminWidget Chainable
    */
    public function set_type($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Template ident must be a string'
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
    * @return string
    */
    public function type()
    {
        return $this->type;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException if the ident is not a string
    * @return AdminWidget (Chainable)
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.');
        }
        $this->ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * @param mixed $label
    * @return AdminWidget Chainable
    */
    public function set_label($label)
    {
        $this->label = new TranslationString($label);
        return $this;
    }

    /**
    * @return string
    */
    public function label()
    {
        if ($this->label === null) {
            // Generate label from ident
            $label = ucwords(str_replace(['_', '.', '/'], ' ', $this->ident()));
            $this->label = new TranslationString($label);
        }
        return $this->label;
    }

    public function actions()
    {
        return [];
    }

    /**
    * @param boolean show
    * @throws InvalidArgumentException
    * @return AdminWidget Chainable
    */
    public function set_show_actions($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show actions must be a boolean');
        }
        $this->show_actions = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_actions()
    {
        if ($this->show_actions !== false) {
            return (count($this->actions()) > 0);
        } else {
            return false;
        }
    }

    /**
    * @param boolean show
    * @throws InvalidArgumentException
    * @return AdminWidget Chainable
    */
    public function set_show_label($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException('Show actions must be a boolean');
        }
        $this->show_label = $show;
        return $this;
    }

    /**
    * @return boolean
    */
    public function show_label()
    {
        if ($this->show_label !== false) {
            return ((string)$this->label() == '');
        } else {
            return false;
        }
    }

    /**
    * @param mixed $template Unused
    * @return string
    */
    public function render($template = null)
    {
        unset($template);
        $view = new WidgetView();
        $view->set_context($this);
        $content = $view->render_template($this->ident());
        return $content;
    }
}
