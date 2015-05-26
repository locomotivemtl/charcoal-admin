<?php

namespace Charcoal\Admin\Template;

use \Charcoal\Admin\Template as Template;
use \Charcoal\Admin\Ui\DashboardContainerInterface as DashboardContainerInterface;
use \Charcoal\Admin\Ui\DashboardContainerTrait as DashboardContainerTrait;
use \Charcoal\Admin\Widget as Widget;
use \Charcoal\Admin\Widget\Layout as Layout;
use \Charcoal\Admin\Widget\Dashboard as Dashboard;

class Home extends Template implements DashboardContainerInterface
{
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
                    "columns"=>["2", "1"]
                ]
            ]
        ]);
        $dashboard->set_widgets([
            'reference'=>[
                'type'=>'charcoal/admin/widget/form',
                'label'=>'Form label',
                'form_properties'=>[
                    'foo'=>[
                        'label'=>'Foo property',
                        'type'=>'string'
                    ],
                    'bar'=>[
                        'label'=>'Bar property',
                        'type'=>'string',
                        'input_type'=>'charcoal/admin/property/input/textarea'
                    ]
                ],
                'groups'=>[
                    'main'=>[
                        'label'=>'Group 1 label',
                        'title'=>'Group 1 title',
                        'properties'=>[
                            'foo',
                            'bar'
                        ]
                    ],
                    'second'=>[
                        'type'=>'widget',
                        'widget_type'=>'charcoal/admin/widget/text',
                        'title'=>'Group 2 title',
                        'subtitle'=>'Group 2 subtitle',
                        'description'=>'Foo bar',
                        'notes'=>'Notes notes notes'
                    ]
                ]
            ],
            'test'=>[
                'type'=>'charcoal/admin/widget/text',
                'title'=>'Text Title',
                'subtitle'=>'Text subtitle',
                'description'=>'Text description',
                'notes'=>'Text notes'
            ]
        ]);
        return $dashboard;
    }
}
