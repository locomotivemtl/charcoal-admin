<?php

namespace Charcoal\Admin\Action\Cli\Object\Metadata;

use \Charcoal\Action\CliAction as CliAction;

use \Charcoal\Model\ModelFactory as ModelFactory;

/**
* Edit an object's metadata (JSON file).
*/
class Edit extends CliAction
{
    public function __construct()
    {
        $arguments = $this->default_arguments();
        $this->set_arguments($arguments);

    }

    public function default_arguments()
    {
        $arguments = [
            'obj-type' => [
                'longPrefix'   => 'obj-type',
                'description'  => 'Object type',
                'defaultValue' => ''
            ]
        ];

        $arguments = array_merge(parent::default_arguments(), $arguments);
        return $arguments;
    }

    public function run()
    {
        $climate = $this->climate();

        $climate->underline()->out('Edit object metadata');

        if ($climate->arguments->defined('help')) {
            $climate->usage();
            die();
        }

        $climate->arguments->parse();
        $verbose = !$climate->arguments->get('quiet');

        $obj_type = $this->arg_or_input('obj-type');
        try {
            $this->set_obj_type($obj_type);
            $obj = ModelFactory::instance()->get($obj_type);

            $metadata = $obj->metadata();
            $properties = $obj->properties();

           $climate->out('This script is not working.');

            $climate->green()->out("\n".'Success!');

        } catch (\Exception $e) {
            $climate->error($e->getMessage());
            die();
        }
    }

    /**
    * @param string $obj_type
    * @throws InvalidArgumentException
    * @return Edit Chainable
    */
    public function set_obj_type($obj_type)
    {
        if (!is_string($obj_type)) {
            throw new InvalidArgumentException('Obj type needs to be a string.');
        }
        $this->_obj_type = $obj_type;
        return $this;
    }

    /**
    * @return array
    */
    public function response()
    {
        return [
            'success'=>$this->success()
        ];
    }
}
