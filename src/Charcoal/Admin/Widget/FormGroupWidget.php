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
     * @var array $groupProperties
     */
    private $groupProperties = [];

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
     * @var boolean $showTitle
     */
    private $showTitle = true;

    /**
     * If it is set to false, will disable display of description
     * @var boolean $showDescription
     */
    private $showDescription = true;

    /**
     * If it is set to false, will disable display of the notes (footer).
     * @var boolean $showNotes
     */
    private $showNotes = true;

    /**
     * @var boolean $showHeader
     */
    private $showHeader = true;

    /**
     * @var boolean $showFooter
     */
    private $showFooter = true;

    /**
     * @var string
     * @return FormGroupWidget Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['properties']) && $data['properties'] !== null) {
            $this->setGroupProperties($data['properties']);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function widgetType()
    {
        return 'charcoal/admin/widget/formGroup';
    }

    /**
     * Sets the widget options
     */
    public function setWidgetOptions($opts)
    {
        if (!$opts) {
            return $this;
        }
        $this->widgetOptions = $opts;

        return $this;
    }

    public function widgetOptions()
    {
        return $this->widgetOptions;
    }

    public function jsonWidgetOptions()
    {
        if (!$this->widgetOptions()) {
            return false;
        }

        return json_encode($this->widgetOptions());
    }

    /**
     * @param LayoutWidget|array
     * @throws InvalidArgumentException
     * @return FormGroupWidget Chainable
     */
    public function setLayout($layout)
    {
        if (($layout instanceof LayoutWidget)) {
            $this->layout = $layout;
        } else if (is_array($layout)) {
            $l = new LayoutWidget([
                'logger'=>$this->logger
            ]);
            $l->setData($layout);
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





    public function setGroupProperties($properties)
    {
        $this->groupProperties = $properties;
        return $this;
    }

    public function groupProperties()
    {
        return $this->groupProperties;

    }

    public function formProperties()
    {
        $groupProperties = $this->groupProperties();
        $formProperties = $this->form()->formProperties($groupProperties);

        $ret = [];
        foreach ($formProperties as $property_ident => $property) {
            if (in_array($property_ident, $groupProperties)) {
                //var_dump($property);
                if (is_callable([$this->form(), 'obj'])) {
                    $val = $this->form()->obj()->p($property_ident)->val();
                    $property->setPropertyVal($val);
                }
                yield $property_ident => $property;
            }
        }
    }

    /**
     * @var mixed $description The group title.
     * @return FormGroupWidget Chainable
     */
    public function setTitle($title)
    {
        $this->title = new TranslationString($title);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @var mixed $description The group description.
     * @return FormGroupWidget Chainable
     */
    public function setDescription($description)
    {
        $this->description = new TranslationString($description);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @param mixed $notes The group notes.
     * @return FormGroupWidget Chainable
     */
    public function setNotes($notes)
    {
        $this->notes = new TranslationString($notes);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function notes()
    {
        return $this->notes;
    }

    /**
     * @param boolean $show The show title flag.
     * @return FormGroup Chainable
     */
    public function setShowTitle($show)
    {
        $this->showTitle = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showTitle()
    {
        if ($this->showTitle === false) {
            return false;
        } else {
            return !!$this->title();
        }
    }

    /**
     * @param boolean $show The show description flag.
     * @return FormGroup Chainable
     */
    public function setShowDescription($show)
    {
        $this->showDescription = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showDescription()
    {
        return true;
    }

    /**
     * @param boolean $show The show notes flag.
     * @return FormGroup Chainable
     */
    public function setShowNotes($show)
    {
        $this->showNotes = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showNotes()
    {
        if ($this->showNotes === false) {
            return false;
        } else {
            $notes = $this->notes();
            return !!$notes;
        }
    }

    /**
     * @param boolean $show The show header flag.
     * @return FormGroup Chainable
     */
    public function setShowHeader($show)
    {
        $this->showHeader = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showHeader()
    {
        return true;
    }

    /**
     * @param boolean $show The show footer flag.
     * @return FormGroup Chainable
     */
    public function setShowFooter($show)
    {
        $this->showFooger = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showFooter()
    {
        if ($this->showFooter === false) {
            return false;
        } else {
            return $this->showNotes();
        }
    }
}
