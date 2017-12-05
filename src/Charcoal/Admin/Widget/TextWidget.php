<?php

namespace Charcoal\Admin\Widget;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;

/**
 *
 */
class TextWidget extends AdminWidget
{
    /**
     * @var boolean
     */
    private $showTitle = true;

    /**
     * @var boolean
     */
    private $showSubtitle = true;

    /**
     * @var boolean
     */
    private $showDescription = true;

    /**
     * @var boolean
     */
    private $showNotes = true;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $title;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $subtitle;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $description;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $notes;

    /**
     * @param boolean $show The show title flag.
     * @return self
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
     * @param boolean $show The show subtitle flag.
     * @return self
     */
    public function setShowSubtitle($show)
    {
        $this->showSubtitle = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showSubtitle()
    {
        if ($this->showSubtitle === false) {
            return false;
        } else {
            return !!$this->subtitle();
        }
    }

    /**
     * @param boolean $show The show description flag.
     * @return self
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
        if ($this->showDescription === false) {
            return false;
        } else {
            return !!$this->description();
        }
    }

    /**
     * @param boolean $show The "show notes" flag.
     * @return self
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
            return !!$this->notes();
        }
    }

    /**
     * @param mixed $title The text widget title.
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @param mixed $subtitle The text widget subtitle.
     * @return self
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $this->translator()->translation($subtitle);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $description The text widget description (main content).
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $this->translator()->translation($description);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @param mixed $notes The text widget notes.
     * @return self
     */
    public function setNotes($notes)
    {
        $this->notes = $this->translator()->translation($notes);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function notes()
    {
        return $this->notes;
    }
}
