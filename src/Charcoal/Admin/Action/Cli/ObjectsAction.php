<?php

namespace Charcoal\Admin\Action\Cli;

use \Charcoal\Admin\Action\CliAction as CliAction;

use \Charcoal\Model\ModelFactory as ModelFactory;

use \Charcoal\Loader\CollectionLoader as CollectionLoader;

class ObjectsAction extends CliAction
{
    public function default_arguments()
    {
        $arguments = [
            'obj-type' => [
                'longPrefix'   => 'obj-type',
                'description'  => 'Object type. Leave empty to enter it interactively.',
                'defaultValue' => ''
            ]
        ];

        $arguments = array_merge(parent::default_arguments(), $arguments);
        return $arguments;
    }

    public function run()
    {
        $climate = $this->climate();

        $climate->underline()->out('List objects');

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

            $loader = new CollectionLoader();
            $loader->set_model($obj);
            //$loader->set_source($obj->source());

            $props = array_keys($obj->properties());

            $collection = $loader->load();
            $table = [];

            foreach ($collection as $c) {
                $obj = [];
                foreach ($props as $p) {
                    $prop = $c->p($p);
                    $obj[$prop->label()] = (string)$prop;
                }
                $table[] = $obj;

            }
            $climate->table($table);

        } catch (\Exception $e) {
            //$climate->dump($e);
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
