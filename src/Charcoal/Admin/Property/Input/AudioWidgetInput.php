<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;
use UnexpectedValueException;

// From Mustache
use Mustache_LambdaHelper as LambdaHelper;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\AudioInput;

/**
 * Audio Widget Property Input
 */
class AudioWidgetInput extends AudioInput
{
    const INPUT_TEXT    = 'text';
    const INPUT_CAPTURE = 'capture';
    const INPUT_UPLOAD  = 'upload';

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
    private $captureEnabled = true;

    /**
     * Whether file upload is enabled.
     *
     * @var boolean
     */
    private $uploadEnabled = true;

    /**
     * URL for the "audio recorder" plugin.
     *
     * @var string
     */
    private $recorderPluginUrl;

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
    protected $activePane;

    /**
     * The current rendering context for the audio widget.
     *
     * @var string
     */
    private $currentContext;

    /**
     * Retrieve the control type for the HTML element `<input>`.
     *
     * @return string
     */
    public function type()
    {
        return 'hidden';
    }

    /**
     * @return boolean
     */
    public function displayAudioWidget()
    {
        return $this->textEnabled() || $this->captureEnabled() || $this->uploadEnabled();
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
     * @param  boolean $captureEnabled If recording is enabled or not for this widget.
     * @return self
     */
    public function setCaptureEnabled($captureEnabled)
    {
        $this->captureEnabled = !!$captureEnabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function captureEnabled()
    {
        return $this->captureEnabled;
    }

    /**
     * @deprecated In favour of {@see self::setCaptureEnabled()}
     *
     * @param  boolean $recordingEnabled If recording is enabled or not for this widget.
     * @return self
     */
    public function setRecordingEnabled($recordingEnabled)
    {
        $this->captureEnabled = !!$recordingEnabled;
        return $this;
    }

    /**
     * @deprecated In favour of {@see self::captureEnabled()}
     *
     * @return boolean
     */
    public function recordingEnabled()
    {
        return $this->captureEnabled;
    }

    /**
     * @param  boolean $uploadEnabled If file upload is enabled or not for this widget.
     * @return self
     */
    public function setUploadEnabled($uploadEnabled)
    {
        $this->uploadEnabled = !!$uploadEnabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function uploadEnabled()
    {
        return $this->uploadEnabled;
    }

    /**
     * @deprecated In favour of {@see self::setUploadEnabled()}
     *
     * @param  boolean $fileEnabled If file upload is enabled or not for this widget.
     * @return self
     */
    public function setFileEnabled($fileEnabled)
    {
        $this->uploadEnabled = !!$fileEnabled;
        return $this;
    }

    /**
     * @deprecated In favour of {@see self::uploadEnabled()}
     *
     * @return boolean
     */
    public function fileEnabled()
    {
        return $this->uploadEnabled;
    }

    /**
     * @param  string $url The recording/exporting plugin URL.
     * @return self
     */
    public function setRecorderPluginUrl($url)
    {
        $this->recorderPluginUrl = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function recorderPluginUrl()
    {
        if (!$this->showFilePicker()) {
            return null;
        }

        return $this->recorderPluginUrl;
    }

    /**
     * Necessary evil to render the audio recording plugin URL
     * with the correct object model context.
     *
     * @see \Charcoal\Admin\Property\Input\FileInput::prepareFilePickerUrl()
     * @see \Charcoal\Admin\Property\Input\TinymceInput::prepareFilePickerUrl()
     *
     * @return callable|null
     */
    public function prepareRecorderPluginUrl()
    {
        if ($this->recorderPluginUrl !== null) {
            return null;
        }

        $uri = 'assets/admin/scripts/vendors/recorderjs/recorder.js';
        $uri = '{{# withBaseUrl }}'.$uri.'{{/ withBaseUrl }}';

        return function ($noop, LambdaHelper $helper) use ($uri) {
            $uri = $helper->render($uri);
            $this->recorderPluginUrl = $uri;

            return null;
        };
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
        if ($activePane === null || $activePane === '') {
            $this->activePane = null;
            return $this;
        }

        $validPanes = [
            static::INPUT_TEXT,
            static::INPUT_CAPTURE,
            static::INPUT_UPLOAD,
        ];

        if (!in_array($activePane, $validPanes)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid input "%s" for Audio Property Input',
                $activePane
            ));
        }

        $this->activePane = $activePane;
        return $this;
    }

    /**
     * Retrieve the active widget pane based on the property's values.
     *
     * @return string
     */
    public function resolveActivePane()
    {
        if ($this->hasAudioPropertyVal()) {
            return static::INPUT_UPLOAD;
        }

        return static::INPUT_TEXT;
    }

    /**
     * Retrieve the active widget pane.
     *
     * @return string
     */
    public function activePane()
    {
        if ($this->activePane === null) {
            return $this-> resolveActivePane();
        }

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
        return $this->textProperty()['ident'];
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

        if ($this->textProperty()['l10n']) {
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
     * Retrieve the input ID for the TTS property.
     *
     * @return string
     */
    public function textInputId()
    {
        return 'audio_text_'.$this->inputId();
    }

    /**
     * Retrieve the input ID for the audio recorder property.
     *
     * @return string
     */
    public function captureInputId()
    {
        return 'audio_capture_'.$this->inputId();
    }

    /**
     * Retrieve the input ID for the audio file property.
     *
     * @return string
     */
    public function uploadInputId()
    {
        return 'audio_upload_'.$this->inputId();
    }

    /**
     * Retrieve the input ID for the widget's hidden property.
     *
     * @return string
     */
    public function hiddenInputId()
    {
        return 'audio_hidden_'.$this->inputId();
    }

    /**
     * Change the property input context to that of the text-to-speech property.
     *
     * @return callable|null
     */
    public function textPropertyContext()
    {
        if (!$this->textEnabled() || $this->currentContext) {
            return null;
        }

        $this->currentContext = static::INPUT_TEXT;

        $baseInputId = $this->inputId();
        $textInputId = $this->textInputId();

        return function ($template, LambdaHelper $helper) use ($baseInputId, $textInputId) {
            $this->setInputId($textInputId);
            $template = $helper->render($template);
            $this->setInputId($baseInputId);

            $this->currentContext = null;

            return $template;
        };
    }

    /**
     * Change the property input context to that of the audio recorder property.
     *
     * @return callable|null
     */
    public function capturePropertyContext()
    {
        if (!$this->captureEnabled() || $this->currentContext) {
            return null;
        }

        $this->currentContext = static::INPUT_CAPTURE;

        $baseInputId    = $this->inputId();
        $captureInputId = $this->captureInputId();

        return function ($template, LambdaHelper $helper) use ($baseInputId, $captureInputId) {
            $this->setInputId($captureInputId);
            $template = $helper->render($template);
            $this->setInputId($baseInputId);

            $this->currentContext = null;

            return $template;
        };
    }

    /**
     * Change the property input context to that of the audio file property.
     *
     * @return callable|null
     */
    public function uploadPropertyContext()
    {
        if (!$this->uploadEnabled() || $this->currentContext) {
            return null;
        }

        $this->currentContext = static::INPUT_UPLOAD;

        $baseInputId   = $this->inputId();
        $uploadInputId = $this->uploadInputId();

        return function ($template, LambdaHelper $helper) use ($baseInputId, $uploadInputId) {
            $this->setInputId($uploadInputId);
            $template = $helper->render($template);
            $this->setInputId($baseInputId);

            $this->currentContext = null;

            return $template;
        };
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        $inputId = $this->inputId();
        $data    = parent::controlDataForJs();

        return array_replace($data, [
            // Audio Control
            'active_pane'         => $this->resolveActivePane(),
            'text_input_id'       => $this->textInputId(),
            'capture_input_id'    => $this->captureInputId(),
            'upload_input_id'     => $this->uploadInputId(),
            'hidden_input_id'     => $this->hiddenInputId(),
            'recorder_plugin_url' => $this->recorderPluginUrl(),
        ]);
    }
}
