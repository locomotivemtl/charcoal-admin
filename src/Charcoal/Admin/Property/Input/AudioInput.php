<?php

namespace Charcoal\Admin\Property\Input;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Property\AbstractPropertyInput as AbstractPropertyInput;

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
