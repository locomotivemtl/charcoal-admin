<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Audio Property Input
 */
class AudioInput extends AbstractPropertyInput
{
    private $message;
    private $audio_data;
    private $audio_file;

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function message()
    {
        // var_dump($this->message);
        return $this->message;
    }
}
