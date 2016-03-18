<?php

namespace Charcoal\Admin\Property\Input;

use \Charcoal\Translation\TranslationString;

use \Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * DateTime Picker input
 */
class DateTimePickerInput extends AbstractPropertyInput
{
    /**
     * @var TranslationStringInterface $placeholder
     */
    private $placeholder;

    /**
     * @param mixed $placeholder The placeholder attriubute.
     * @return Text Chainable
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = new TranslationString($placeholder);
        return $this;
    }

    /**
     * @see    TextInput::placeholder()
     * @return string
     */
    public function placeholder()
    {
        return $this->placeholder;
    }
}
