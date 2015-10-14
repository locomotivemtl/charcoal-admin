<?php

namespace Charcoal\Admin\Action\Cli\Object;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Admin\Action\CliAction;

use \Charcoal\Model\ModelFactory;

class CreateAction extends CliAction
{

    /**
    * @return array
    */
    public function default_arguments()
    {
        $arguments = [
            'obj-type' => [
                'longPrefix'   => 'obj-type',
                'description'  => 'Object type',
                'defaultValue' => ''
            ],
            'obj-id' => [
                'longPrefiex'  => 'obj-id',
                'description'  => 'Object ID',
                'defaultValue' => ''
            ]
        ];

        $arguments = array_merge(parent::default_arguments(), $arguments);
        return $arguments;
    }

    public function run()
    {
        $climate = $this->climate();

        $climate->underline()->out('Create a new object');

        if ($climate->arguments->defined('help')) {
            $climate->usage();
            die();
        }

        $climate->arguments->parse();
        $verbose = !$climate->arguments->get('quiet');

        $obj_type = $this->arg_or_input('obj-type');
        $obj_id = $this->arg_or_input('obj-id');
        try {
            $this->set_obj_type($obj_type);
            $obj = ModelFactory::instance()->get($obj_type);

            $properties = $obj->properties();

            $vals = [];
            foreach ($properties as $prop) {
                //$input = $climate->input(sprintf('Enter value for "%s":', $prop->label()));
                $input = $this->property_to_input($prop);
                $vals[$prop->ident()] = $input->prompt();
            }

            $obj->set_flat_data($vals);
            $ret = $obj->save();

            $climate->green()->out("\n".sprintf('Success! Object "%s" created.', $ret));

        } catch (\Exception $e) {
            $climate->error($e->getMessage());
            die();
        }
    }

    public function property_to_input($prop)
    {
        $climate = $this->climate();

        if ($prop->type() == 'password') {
            $input = $climate->password(sprintf('Enter value for "%s":', $prop->label()));
        } else if ($prop->type() == 'boolean') {
            $opts = [
                1 => $prop->true_label(),
                0 => $prop->false_label()
            ];
            $input = $climate->radio(sprintf('Enter value for "%s":', $prop->label()), $opts);
        } else {
            $input = $climate->input(sprintf('Enter value for "%s":', $prop->label()));
            if ($prop->type() == 'text' || $prop->type == 'html') {
                $input->multiLine();
            }
        }
        return $input;
    }

    public function set_obj_type($obj_type)
    {
        if (!is_string($obj_type)) {
            throw new InvalidArgumentException('Obj type needs to be a string.');
        }
        $this->_obj_type = $obj_type;
        return $this;
    }

    public function response()
    {
        return [
            'success'=>$this->success()
        ];
    }
}
