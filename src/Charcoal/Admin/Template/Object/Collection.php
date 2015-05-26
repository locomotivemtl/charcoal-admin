<?php

namespace Charcoal\Admin\Template\Object;

use \Charcoal\Admin\Template\Object as ObjectTemplate;
use \Charcoal\Admin\Ui\CollectionContainerInterface as CollectionContainerInterface;
use \Charcoal\Admin\Ui\CollectionContainerTrait as CollectionContainerTrait;
use \Charcoal\Admin\Ui\DashboardContainerInterface as DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait as DashboardContainerTrait;

use \Charcoal\Admin\Widget as Widget;
use \Charcoal\Admin\Widget\Layout as Layout;
use \Charcoal\Admin\Widget\Dashboard as Dashboard;

// From `charcoal-base`
use \Charcoal\Widget\WidgetFactory as WidgetFactory;

class Collection extends ObjectTemplate implements CollectionContainerInterface, DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;


    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return Edit Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }
        parent::set_data($data);
        
        $this->set_collection_data($data);
        $this->set_dashboard_data($data);

        return $this;
    }

    /**
    * @return Dashboard
    */
    public function create_dashboard($data = null)
    {
        $dashboard_ident = $this->dashboard_ident();
        $dashboard_config = $this->dashboard_config();
        $dashboard = new Dashboard();
        if ($data !== null) {
            $dashboard->set_data($data);
        }
        $dashboard->set_layout([
            'structure'=>[
                [
                    "num_columns"=>3,
                    "columns"=>["1", "2"]
                ]
            ]
        ]);
        $dashboard->set_widgets([
            'list'=>[
                'type'=>'charcoal/admin/widget/table',
                'label'=>'Table label'
            ]
        ]);
        return $dashboard;
    }

    public function objects()
    {
        return [
            [
                'properties'=>[
                    [
                        'label'=>'Foo'
                    ],
                    [
                        'label'=>'Bar'
                    ]
                ]
            ],
            [
                'properties'=>[
                   [
                        'label'=>'Hello'
                    ],
                    [
                        'label'=>'World!'
                    ]
                ]
            ]
        ];
    }

    public function has_objects()
    {
        return !!count($this->objects());
    }
}
