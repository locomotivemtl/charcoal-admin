<?php

namespace Charcoal\Admin\Action\Cli\Object\Table;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


use \Charcoal\Action\CliAction as CliAction;

use \Charcoal\Model\ModelFactory as ModelFactory;

/**
* Create an object's table (sql source) according to its metadata's properties.
*/
class CreateAction extends CliAction
{
    /**
    *
    */
    public function __construct()
    {
        $arguments = $this->default_arguments();
        $this->set_arguments($arguments);
    }

    /**
    * Make the class callable
    *
    * @param ServerRequestInterface $request
    * @param ResponseInterface $response
    * @return ResponseInterface
    */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->run($request, $response);
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
    * @param ServerRequestInterface $request
    * @param ResponseInterface $response
    * @return ResponseInterface
    */
    public function run(ServerRequestInterface $request, ResponseInterface $response)
    {
        unset($request); // Unused

        $climate = $this->climate();

        $climate->underline()->out('Create object table');

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
            $climate->bold()->out(sprintf('The table "%s" will be created...', $table));

            $input = $climate->confirm('Continue?');
            if (!$input->confirmed()) {
                return $response;
            }

            if ($source->table_exists()) {
                $climate->error(sprintf('The table "%s" already exists. This script can only create new tables.', $table));
                $climate->darkGray()->out('If you want to alter the table with the latest object\'s metadata, run the `admin/object/table/alter` script.');
                return $response;
            }


            $ret = $source->create_table();

            $climate->green()->out("\n".'Success!');

        } catch (\Exception $e) {
            $climate->error($e->getMessage());
        }

        return $response;
    }

    /**
    * @param string $obj_type
    * @throws InvalidArgumentException
    * @return Create Chainable
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
            'success'=>$this->success(),
            'feedbacks'=>$this->feedbacks()
        ];
    }
}
