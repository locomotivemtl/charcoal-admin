<?php

namespace Charcoal\Admin\Property\Display;

use Charcoal\Admin\Property\AbstractPropertyDisplay;

/**
 * Textual Display Property
 *
 * The default display for most properties; only output {@see AbstractProperty::displayVal()}.
 */
class TextDisplay extends AbstractPropertyDisplay
{
    /**
     * @return string
     */
    public function displayVal()
    {
        $text = parent::displayVal();

        return nl2br($text);
    }
}
