<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Translation\TranslationString;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Template\TemplateViewController as TemplateViewController;

// From `charcoal-base`
use \Charcoal\Model\ModelFactory;

class MapWidget extends AdminWidget
{
    /**
    * @var string
    */
    private $_widget_type = '';

    /**
    * @var Object $_actions
    */
    private $_actions;

    /**
    * @var object styles (concerning the marker style)
    */
    private $_styles;

    protected $_sidebar_sidebar_properties = [];
    protected $_priority;

    /**
    * @var TranslationString $_title
    * @var TranslationString $_subtitle
    */
    protected $_title;
    protected $_subtitle;

    private $_lat_property;
    private $_lon_property;

    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['title'])) {
            $this->set_title($data['title']);
        }
        if (isset($data['subtitle'])) {
            $this->set_title($data['subtitle']);
        }
        if (isset($data['actions'])) {
            $this->set_actions($data['actions']);
        }
        if (isset($data['lat_property'])) {
            $this->set_lat_property($data['lat_property']);
        }
        if (isset($data['lon_property'])) {
            $this->set_lon_property($data['lon_property']);
        }
        return $this;
    }

    public function set_lat_property( $p )
    {
        $this->_lat_property = $p;
    }

    public function lat_property()
    {
        return $this->_lat_property;
    }
    public function set_lon_property( $p )
    {
        $this->_lon_property = $p;
    }
    public function lon_property()
    {
        return $this->_lon_property;
    }

    public function lat()
    {
        if (!$this->obj()) {
            return false;
        }
        $obj = $this->obj();
        return call_user_func([$obj, $this->lat_property()]);
    }

    public function lon()
    {
        if (!$this->obj()) {
            return false;
        }
        $obj = $this->obj();
        return call_user_func([$obj, $this->lon_property()]);
    }

    /**
    * Styles
    */
    public function styles()
    {

    }

    public function set_styles($styles)
    {
        if (!$styles) {
            return $this;
        }

        return $this;
    }

    public function obj()
    {
        $obj = null;
        $id = ( isset($_GET['obj_id']) ? $_GET['obj_id'] : 0 );
        $obj_type = ( isset($_GET['obj_type']) ? $_GET['obj_type'] : 0 );
        if ($id && $obj_type) {
            $obj = ModelFactory::instance()->get($obj_type);
            $obj->load($id);
        }
        return $obj;
    }


    /**
    * Title and subtitle getter/setters
    * @param {Mixed} - l10n object OR string
    * @return (setters) $this (chainable)
    * @return (getters) String
    */
    public function set_subtitle($subtitle)
    {
        if ($subtitle === null) {
            $this->_title = null;
        } else {
            $this->_title = new TranslationString($subtitle);
        }
    }

    public function subtitle()
    {
        return $this->_subtitle;
    }

    public function set_title($title)
    {
        if ($title === null) {
            $this->_title = null;
        } else {
            $this->_title = new TranslationString($title);
        }
        return $this;
    }

    public function title()
    {
        if ($this->_title === null) {
            $this->set_title('Actions');
        }
        return $this->_title;
    }

    public function priority()
    {
        return 2;
    }
}
