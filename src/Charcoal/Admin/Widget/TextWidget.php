<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

use \Charcoal\Admin\AdminWidget;

use \Charcoal\Translation\TranslationString;

/**
 *
 */
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
     * @return Text Chainable
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
     * @param boolean $show
     * @return Text Chainable
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
     * @param boolean $show
     * @throws InvalidArgumentException
     * @return Text Chainable
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
     * @param boolean $show
     * @throws InvalidArgumentException
     * @return Text Chainable
     */
    public function setShowNotes($show)
    {
        if (!is_bool($show)) {
            throw new InvalidArgumentException(
                'Show must be a boolean'
            );
        }
        $this->showNotes = $show;
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
     * @return TextWidget Chainable
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
     * @param mixed $subtitle The text widget subtitle.
     * @return TextWidget Chainable
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = new TranslationString($subtitle);
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $description The text widget description (main content).
     * @return TextWidget Chainable
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
     * @param mixed $notes The text widget notes.
     * @return TextWidget Chainable
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
}
