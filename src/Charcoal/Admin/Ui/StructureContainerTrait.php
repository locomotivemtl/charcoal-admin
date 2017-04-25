<?php

namespace Charcoal\Admin\Ui;

use RuntimeException;
use OutOfBoundsException;
use UnexpectedValueException;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\AbstractFormGroup;
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\FormInput\FormInputInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\FormGroupWidget;
use Charcoal\Admin\Ui\ObjectContainerInterface;

/**
 * An implementation, as Trait, of the {@see \Charcoal\Admin\Ui\StructureContainerInterface}.
 */
trait StructureContainerTrait
{
    /**
     * How to display the structure properties.
     *
     * @var string|null
     */
    protected $display;

    /**
     * How to display an empty structure.
     *
     * @var boolean
     */
    protected $showEmpty = true;

    /**
     * Set the property's display layout.
     *
     * @param  string $display The layout for the tickable elements.
     * @throws InvalidArgumentException If the given layout is invalid.
     * @throws OutOfBoundsException If the given layout is unsupported.
     * @return self
     */
    public function setDisplay($display)
    {
        if ($display === null) {
            $this->display = null;

            return $this;
        }

        if (!is_string($display)) {
            throw new InvalidArgumentException(sprintf(
                'Layout must be a string, received %s',
                (is_object($display) ? get_class($display) : gettype($display))
            ));
        }

        $supportedLayouts = $this->supportedDisplayLayouts();
        if (!in_array($display, $supportedLayouts)) {
            throw new OutOfBoundsException(sprintf(
                'Unsupported layout [%s]; must be one of %s',
                $display,
                implode(', ', $supportedLayouts)
            ));
        }

        $this->display = $display;

        return $this;
    }

    /**
     * Retrieve the property's display layout.
     *
     * @return string|null
     */
    public function display()
    {
        if ($this->display === null) {
            return $this->defaultDisplay();
        }

        return $this->display;
    }

    /**
     * Retrieve the display layouts; for templating.
     *
     * @return array
     */
    public function displays()
    {
        $supported = $this->supportedDisplayLayouts();
        $displays  = [];
        foreach ($supported as $display) {
            $displays[$display] = ($display === $this->display());
        }

        return $displays;
    }

    /**
     * Retrieve the supported display layouts.
     *
     * @return array
     */
    protected function supportedDisplayLayouts()
    {
        return [
            self::GROUP_STRUCT_DISPLAY,
            self::SEAMLESS_STRUCT_DISPLAY
        ];
    }

    /**
     * Retrieve the default display layout.
     *
     * @return array
     */
    protected function defaultDisplay()
    {
        return static::GROUP_STRUCT_DISPLAY;
    }

    /**
     * Show/hide the notice when the structure is empty.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the notice.
     * @return self
     */
    public function setShowEmpty($show)
    {
        $this->showEmpty = !!$show;

        return $this;
    }

    /**
     * Determine if a notice should be displayed when the structure is empty.
     *
     * @return boolean
     */
    public function showEmpty()
    {
        return $this->showEmpty;
    }
}
