<?php

namespace Charcoal\Admin\Template\Object;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Admin\Template\Object as ObjectTemplate;
use \Charcoal\Admin\Widget as Widget;
use \Charcoal\Admin\Widget\Layout as Layout;
use \Charcoal\Admin\Widget\Dashboard as Dashboard;

// From `charcoal-base`
use \Charcoal\Widget\WidgetFactory as WidgetFactory;

class Edit extends ObjectTemplate
{
    private $_obj_id;
    private $_dashboard_ident;
    private $_dashboard_config;
    protected $_dashboard;

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
        
        if (isset($data['dashboard_ident'])) {
            $this->set_dashboard_ident($data['dashboard_ident']);
        }
        if (isset($data['dashboard_config'])) {
            $this->set_dashboard_config($data['dashboard_config']);
        }

        return $this;
    }

    /**
    * @param string $dashboard_ident
    * @throws InvalidArgumentException
    * @return Edit Chainable
    */
    public function set_dashboard_ident($dashboard_ident)
    {
        if (!is_string($dashboard_ident)) {
            throw new InvalidArgumentException('Dashboard ident needs to be a string');
        }
        $this->_dashboard_ident = $dashboard_ident;
        return $this;
    }

    /**
    * @return string
    */
    public function dashboard_ident()
    {
        return 'DASHBOARD_IDENT';
    }

    /**
    * @param mixed $dashboard_config
    * @return Edit Chainable
    */
    public function set_dashboard_config($dashboard_config)
    {
        $this->_dashboard_config = $dashboard_config;
        return $this;
    }

    public function dashboard_config()
    {
        if ($this->_dashboard_config === null) {
            $this->create_dashboard_config();
        }
        return $this->_dashboard_config;
    }

    public function create_dashboard_config()
    {
        return null;
    }

    /**
    * @return Dashboard
    */
    public function dashboard()
    {
        if ($this->_dashboard === null) {
            $this->_dashboard = $this->create_dashboard();
        }
        return $this->_dashboard;
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
            'reference'=>[
                'type'=>'charcoal/admin/widget/form',
                'label'=>'Form label',
                'form_properties'=>[
                    'foo'=>[
                        'type'=>'string'
                    ],
                    'bar'=>[
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
