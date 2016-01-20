<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

use \Charcoal\Translation\TranslationString;

use \Charcoal\Admin\AdminWidget;

/**
 *
 */
class FormSidebarWidget extends AdminWidget
{
    /**
     * In-memory copy of the parent form widget.
     * @var FormWidget $form
     */
    private $form;

    /**
     * @var string
     */
    private $widget_type = 'properties';

    /**
     * @var Object $actions
     */
    private $actions;

    protected $sidebar_sidebarProperties = [];
    protected $priority;

    /**
     * @var TranslationString $title
     */
    protected $title;

    public function setForm(FormWidget $form)
    {
        $this->form = $form;
        return $this;
    }

    public function form()
    {
        return $this->form;
    }


    public function setSubtitle($subtitle)
    {
        if ($subtitle === null) {
            $this->title = null;
        } else {
            $this->title = new TranslationString($subtitle);
        }
    }

    public function setSidebarProperties($properties)
    {
        $this->sidebarProperties = $properties;
        return $this;
    }

    public function sidebarProperties()
    {
        return $this->sidebarProperties;
    }

    public function formProperties()
    {
        $sidebarProperties = $this->sidebarProperties();
        $formProperties = $this->form()->formProperties($sidebarProperties);
        $ret = [];
        foreach ($formProperties as $property_ident => $property) {
            if (in_array($property_ident, $sidebarProperties)) {
                if (is_callable([$this->form(), 'obj'])) {
                    $val = $this->form()->obj()->p($property_ident)->val();
                    $property->setProperty_val($val);
                }
                yield $property_ident => $property;
            }
        }
    }

    /**
     * Defined the form actions
     * @param object $actions
     * @return FormGroupWidget Chainable
     */
    public function setActions($actions)
    {
        if (!$actions) {
            return $this;
        }
        $this->actions = [];

        foreach ($actions as $ident => $action) {
            if (!isset($action['url']) || !isset($action['label'])) {
                continue;
            }
            $label = new TranslationString($action['label']);
            $url = $this->form()->obj()->render($action['url']);

            // Info = default
            // Possible: danger, info
            $btn = isset($action['type']) ? $action['type'] : 'info';
            $this->actions[] = [ 'label' => $label, 'url' => $url, 'btn' => $btn ];
        }

        return $this;
    }

    /**
     * Returns the actions as an ArrayIterator
     * [ ['label' => $label, 'url' => $url] ]
     * @see $this->set_actions()
     * @return object actions
     */
    public function actions()
    {
        return $this->actions;
    }

    /**
     * @var integer $priority
     * @throws InvalidArgumentException
     * @return FormGroupWidget Chainable
     */
    public function setPriority($priority)
    {
        if (!is_int($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }
        $priority = (int)$priority;
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $title
     * @return FormSidebarWidget Chainable
     */
    public function setTitle($title)
    {
        if ($title === null) {
            $this->title = null;
        } else {
            $this->title = new TranslationString($title);
        }
        return $this;
    }

    public function title()
    {
        if ($this->title === null) {
            $this->set_title('Actions');
        }
        return $this->title;
    }
}
