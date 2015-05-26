<?php

namespace Charcoal\Admin\Action\Cli\Object;

use \Charcoal\Admin\Action\CliAction as CliAction;

use \Charcoal\Model\ModelFactory as ModelFactory;

class Edit extends CliAction
{
    
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

        $climate->underline()->out('Create object');

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
                $input = $climate->input(sprintf('Enter value for "%s":', $prop->ident()));
                $vals[$prop->ident()] = $input->prompt();
            }

            $obj->set_flat_data($vals);
            $obj->save();

            $climate->green()->out("\n".'Success!');

        } catch (\Exception $e) {
            $climate->error($e->getMessage());
            die();
        }
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
