<?php

namespace Charcoal\Admin\Action\Cli;

use \Exception;
use \InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Module `charcoal-core` dependencies
use \Charcoal\Model\ModelFactory;
use \Charcoal\Loader\CollectionLoader;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\Action\CliAction;
use \Charcoal\Admin\Ui\CollectionContainerInterface as CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait as CollectionContainerTrait;

/**
* Script action to list the objects of a certain type.
*
* ## Required parameters
* > When not running in silent mode, required parameters omitted from the command will be asked interactively.
* - `obj-type`
*
* ## Optional parametrs
* - `num-per-page`
* - `page`
* - `list-ident`
*/
class ObjectsAction extends CliAction implements CollectionContainerInterface
{
    use CollectionContainerTrait;

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
                'description'  => 'Object type. Leave empty to enter it interactively.',
                'defaultValue' => ''
            ],
            'num' => [
                'prefix'       => 'n',
                'longPrefix'   => 'num',
                'description'  => 'Number of objects to retrieve.',
                'defaultValue' => 250,
                'castTo'       => 'int'
            ],
            'page' => [
                'prefix'       => 'p',
                'longPrefix'   => 'page',
                'description'  => 'Current page. Depends on the number of objects.',
                'defaultValue' => 1,
                'castTo'       => 'int'
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

        $climate->underline()->out('List objects');

        if ($climate->arguments->defined('help')) {
            $climate->usage();
            return $response;
        }

        $climate->arguments->parse();
        $verbose = !$climate->arguments->get('quiet');

        try {
            $data = [
                'obj_type'      => $this->arg_or_input('obj-type'),
                'page'          => $climate->arguments->get('page'),
                'num_per_page'  => $climate->arguments->get('num')
            ];

            $this->set_data($data);

            $model = ModelFactory::instance()->get($this->obj_type());

            $loader = new CollectionLoader();
            $loader->set_model($model);
            $loader->set_pagination([
                'page' => $this->page(),
                'num_per_page' => $this->num_per_page()
            ]);

            $collection = $loader->load();
            $collection = $this->collection();
            $table = [];

            $rows = $this->object_rows();
            // ...

            foreach ($collection as $c) {
                $obj = [];
                $props = $model->properties();
                foreach ($props as $property_ident => $unused) {
                    $prop = $c->p($property_ident);
                    $label = (string)$prop->label();
                    $val = (string)$prop->display_val();
                    $obj[$label] = $val;
                }
                $table[] = $obj;

            }
            $climate->table($table);

        } catch (Exception $e) {
            //$climate->out($e->xdebug_message);
            $climate->error($e->getMessage());
        }
        return $response;
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

    public function properties()
    {
        return [];
    }
}
