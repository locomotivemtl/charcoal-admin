<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;
use UnexpectedValueException;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Audio Property Input
 */
class AudioInput extends AbstractPropertyInput
{
    /**
     * Whether text-to-speech is enabled.
     *
     * @var boolean
     */
    private $textEnabled = true;

    /**
     * Whether audio recording is enabled.
     *
     * @var boolean
     */
    private $recordingEnabled = true;

    /**
     * Whether file upload is enabled.
     *
     * @var boolean
     */
    private $fileEnabled = true;

    /**
     * The text property value for TTS.
     *
     * @var mixed
     */
    private $textPropertyVal;

    /**
     * The text property for TTS.
     *
     * @var PropertyInterface
     */
    private $textProperty;

    /**
     * The HTML input name attribute for TTS.
     *
     * @var string
     */
    protected $textInputName;

    /**
     * The active widget pane.
     *
     * @var string
     */
    protected $activePane = 'text';

    /**
     * @return boolean
     */
    public function displayAudioWidget()
    {
        return $this->textEnabled() || $this->recordingEnabled() || $this->fileEnabled();
    }

    /**
     * @param  boolean $textEnabled If TTS is enabled or not for this widget.
     * @return self
     */
    public function setTextEnabled($textEnabled)
    {
        $this->textEnabled = !!$textEnabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function textEnabled()
    {
        return $this->textEnabled;
    }

    /**
     * @param  boolean $recordingEnabled If recording is enabled or not for this widget.
     * @return self
     */
    public function setRecordingEnabled($recordingEnabled)
    {
        $this->recordingEnabled = !!$recordingEnabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function recordingEnabled()
    {
        return $this->recordingEnabled;
    }

    /**
     * @param  boolean $fileEnabled If file upload is enabled or not for this widget.
     * @return self
     */
    public function setFileEnabled($fileEnabled)
    {
        $this->fileEnabled = !!$fileEnabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function fileEnabled()
    {
        return $this->fileEnabled;
    }

    /**
     * Set the active widget pane.
     *
     * @param  string $activePane The active widget pane.
     * @throws InvalidArgumentException If the provided argument is not a string.
     * @return self
     */
    public function setActivePane($activePane)
    {
        if (!is_string($activePane)) {
            throw new InvalidArgumentException(
                'Audio Property Input Pane must be a string.'
            );
        }
        $this->activePane = $activePane;
        return $this;
    }

    /**
     * Retrieve the active widget pane.
     *
     * @return string
     */
    public function activePane()
    {
        return $this->activePane;
    }

    /**
     * Alias of {@see AbstractPropertyInput::setPropertyVal()}.
     *
     * @param  mixed $val The audio property value.
     * @return self
     */
    public function setAudioPropertyVal($val)
    {
        $this->setPropertyVal($val);
        return $this;
    }

    /**
     * Alias of {@see AbstractPropertyInput::propertyVal()}.
     *
     * @return mixed
     */
    public function audioPropertyVal()
    {
        return $this->propertyVal();
    }

    /**
     * @return boolean
     */
    public function hasAudioPropertyVal()
    {
        $prop = $this->audioProperty();
        $val  = $prop->inputVal($this->audioPropertyVal(), [
            'lang' => $this->lang(),
        ]);

        return !empty($val);
    }

    /**
     * Alias of {@see AbstractPropertyInput::setProperty()}.
     *
     * @param  PropertyInterface $p The property for TTS.
     * @return self
     */
    public function setAudioProperty(PropertyInterface $p)
    {
        $this->setProperty($p);
        return $this;
    }

    /**
     * Alias of {@see AbstractPropertyInput::property()}.
     *
     * @return PropertyInterface
     */
    public function audioProperty()
    {
        return $this->property();
    }

    /**
     * Alias of {@see AbstractPropertyInput::propertyIdent()}.
     *
     * @return string
     */
    public function audioPropertyIdent()
    {
        return $this->propertyIdent();
    }

    /**
     * Alias of {@see AbstractPropertyInput::setInputName()}.
     *
     * @param  string $inputName HTML input id attribute.
     * @return self
     */
    public function setAudioInputName($inputName)
    {
        $this->setInputName($inputName);

        return $this;
    }

    /**
     * Alias of {@see AbstractPropertyInput::inputName()}.
     *
     * @return string
     */
    public function audioInputName()
    {
        return $this->inputName();
    }

    /**
     * Alias of {@see AbstractPropertyInput::inputVal()}.
     *
     * @return string
     */
    public function audioInputVal()
    {
        return $this->inputVal();
    }

    /**
     * Set the property value for TTS.
     *
     * @param  mixed $val The property value.
     * @return self
     */
    public function setTextPropertyVal($val)
    {
        $this->textPropertyVal = $val;
        return $this;
    }

    /**
     * Retrieve the property value for TTS.
     *
     * @return mixed
     */
    public function textPropertyVal()
    {
        return $this->textPropertyVal;
    }

    /**
     * @return boolean
     */
    public function hasTextPropertyVal()
    {
        $prop = $this->textProperty();
        $val  = $prop->inputVal($this->textPropertyVal(), [
            'lang' => $this->lang(),
        ]);

        return !empty($val);
    }

    /**
     * Set the property instance for TTS.
     *
     * @param  PropertyInterface $p The property for TTS.
     * @return self
     */
    public function setTextProperty(PropertyInterface $p)
    {
        $this->textProperty = $p;
        return $this;
    }

    /**
     * Retrieve the property instance for TTS.
     *
     * @return PropertyInterface
     */
    public function textProperty()
    {
        return $this->textProperty;
    }

    /**
     * Retrieve the property identifier for TTS.
     *
     * @return string
     */
    public function textPropertyIdent()
    {
        return $this->textProperty()->ident();
    }

    /**
     * Set the input name for TTS.
     *
     * @see    AbstractPropertyInput::setInputName()
     * @param  string $inputName HTML input name attribute.
     * @return self
     */
    public function setTextInputName($inputName)
    {
        $this->textInputName = $inputName;
        return $this;
    }

    /**
     * Retrieve the input name for TTS.
     *
     * @see    AbstractPropertyInput::inputName()
     * @return string
     */
    public function textInputName()
    {
        if ($this->textInputName) {
            $name = $this->textInputName;
        } else {
            $name = $this->textPropertyIdent();
        }

        if ($this->textProperty()->l10n()) {
            $name .= '['.$this->lang().']';
        }

        return $name;
    }

    /**
     * Retrieve the input value for TTS.
     *
     * @see    AbstractPropertyInput::inputVal()
     * @throws UnexpectedValueException If the value is invalid.
     * @return string
     */
    public function textInputVal()
    {
        $prop = $this->textProperty();
        $val  = $prop->inputVal($this->textPropertyVal(), [
            'lang' => $this->lang(),
        ]);

        if ($val === null) {
            return '';
        }

        if (!is_scalar($val)) {
            throw new UnexpectedValueException(sprintf(
                'Property Input Value must be a string, received %s',
                (is_object($val) ? get_class($val) : gettype($val))
            ));
        }

        return $val;
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        return [
            // Audio Control
            'active_pane'          => $this->activePane(),
            'hidden_input_id'      => 'hidden_'.$this->inputId(),
            'recorder_worker_path' => '{{ baseUrl }}assets/admin/scripts/vendors/recorderWorker.js',
        ];
    }
}
