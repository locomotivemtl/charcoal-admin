<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Audio Property Input
 */
class AudioInput extends AbstractPropertyInput
{
    /**
     * @var boolean $textEnabled
     */
    private $textEnabled = true;

    /**
     * @var boolean $recordingEnabled
     */
    private $recordingEnabled = true;

    /**
     * @var boolean $fileEnabled
     */
    private $fileEnabled = true;

    /**
     * @var mixed $message
     */
    private $message;

    /**
     * @var mixed $audio_data
     */
    private $audio_data;

    /**
     * @var mixed $audio_file
     */
    private $audio_file;

    /**
     * @return boolean
     */
    public function displayAudioWidget()
    {
        return $this->textEnabled() || $this->recordingEnabled() || $this->fileEnabled();
    }

    /**
     * @param mixed $message The audio message.
     * @return AudioInput Chainable
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @param boolean $textEnabled If TTS is enabled or not for this widget.
     * @return AudioInput Chainable
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
     * @param boolean $recordingEnabled If recording is enabled or not for this widget.
     * @return AudioInput Chainable
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
      * @param boolean $fileEnabled If file upload is enabled or not for this widget.
      * @return AudioInput Chainable
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
}
