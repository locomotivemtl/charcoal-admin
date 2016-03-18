<?php

namespace Charcoal\Admin;

use \Exception;

// Module `charcoal-app` dependencies
use \Charcoal\App\Script\AbstractScript;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyInterface;

/**
 *
 */
abstract class AdminScript extends AbstractScript
{

    /**
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return mixed
     */
    protected function propertyToInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        if ($prop->type() == 'password') {
            return $this->passwordInput($prop);
        } elseif ($prop->type() == 'boolean') {
            return $this->booleanInput($prop);
        } else {
            $input = $climate->input(
                sprintf('Enter value for "%s":', $prop->label())
            );
            if ($prop->type() == 'text' || $prop->type == 'html') {
                $input->multiLine();
            }
        }
        return $input;
    }

    /**
     * Get a CLI input from a boolean property.
     *
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function booleanInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        $opts = [
            1 => $prop->trueLabel(),
            0 => $prop->falseLabel()
        ];
        $input = $climate->radio(
            sprintf('Enter value for "%s":', $prop->label()),
            $opts
        );
        return $input;
    }

    /**
     * Get a CLI password input (hidden) from a password property.
     *
     * @param PropertyInterface $prop The property to retrieve input from.
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    private function passwordInput(PropertyInterface $prop)
    {
        $climate = $this->climate();

        $input = $climate->password(
            sprintf('Enter value for "%s":', $prop->label())
        );
        return $input;
    }
}
