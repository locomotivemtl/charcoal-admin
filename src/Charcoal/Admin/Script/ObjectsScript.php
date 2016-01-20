<?php

namespace Charcoal\Admin\Script;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Module `charcoal-core` dependencies
use \Charcoal\Model\ModelFactory;
use \Charcoal\Loader\CollectionLoader;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminScript;
use \Charcoal\Admin\Ui\CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait;

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
class ObjectsScript extends AdminScript implements CollectionContainerInterface
{
    use CollectionContainerTrait;

    /**
     * @return array
     */
    public function defaultArguments()
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

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);
// Unused

        $climate = $this->climate();

        $climate->underline()->out('List objects');

        $this->setData([
            'obj_type'      => $this->argOrInput('obj-type'),
            'page'          => $climate->arguments->get('page'),
            'num_per_page'  => $climate->arguments->get('num')
        ]);

        $modelFactory = new ModelFactory();
        $model = $modelFactory->create($this->objType(), [
            'logger' => $this->logger
        ]);

        $loader = new CollectionLoader();
        $loader->set_model($model);
        $loader->set_pagination([
            'page' => $this->page(),
            'num_per_page' => $this->numPerPage()
        ]);

        $collection = $loader->load();
        $collection = $this->collection();
        $table = [];

        $rows = $this->object_rows();

        foreach ($collection as $c) {
            $obj = [];
            $props = $model->properties();
            foreach ($props as $property_ident => $unused) {
                $prop = $c->p($property_ident);
                $label = (string)$prop->label();
                $val = (string)$prop->displayVal();
                $obj[$label] = $val;
            }
            $table[] = $obj;

        }
        $climate->table($table);

        return $response;
    }
}
