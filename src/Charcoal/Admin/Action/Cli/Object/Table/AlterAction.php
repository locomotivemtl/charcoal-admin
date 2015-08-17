<?php

namespace Charcoal\Admin\Action\Cli\Object\Table;

use \Charcoal\Action\CliAction as CliAction;

use \Charcoal\Model\ModelFactory as ModelFactory;

/**
* Alter an object's table (sql source) according to its metadata's properties.
*/
class AlterAction extends CliAction
{
    public function __construct()
    {
        $arguments = $this->default_arguments();
        $this->set_arguments($arguments);

    }

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
            ]
        ];

        $arguments = array_merge(parent::default_arguments(), $arguments);
        return $arguments;
    }

    /**
    * Actually run the script
    */
    public function run()
    {
        $climate = $this->climate();

        $climate->underline()->out('Alert object table');

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

            $source = $obj->source();

            $table = $source->table();
            if ($verbose) {
                $climate->bold()->out(sprintf('The table "%s" will be altered, if necessary...', $table));
            }

            if ($verbose) {
                $metadata = $obj->metadata();
                $properties = $metadata->properties();
                $prop_names = array_keys($properties);
                $climate->out(sprintf("The %d following properties will be used: \"%s\"", count($prop_names), implode(', ', $prop_names)));
            }

            $input = $climate->confirm('Continue?');
            if (!$input->confirmed()) {
                return false;
            }

            if (!$source->table_exists()) {
                $climate->error(sprintf('The table "%s" does not exist. This script can only alter existing tables.', $table));
                $climate->darkGray()->out('If you want to create the table with the latest object\'s metadata, run the `admin/object/table/create` script.');
                die();
            }

            $ret = $source->alter_table();

            $climate->green()->out("\n".'Success!');

        } catch (\Exception $e) {
            $climate->error($e->getMessage());
            die();
        }
    }

    /**
    * @param string
    * @throws InvalidArgumentException
    * @return Alter Chainable
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
    * @return string
    */
    public function obj_type()
    {
        return $this->_obj_type;
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
