<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Translation\TranslationString;
use \Charcoal\Model\ModelFactory;
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\FormGroupTrait;

/**
 * Map Widget displays a google map widget, with UI to add polygons, lines and points.
 *
 * Most of this widget functionalities are in javascript.
 */
class MapWidget extends AdminWidget implements FormGroupInterface
{
    use FormGroupTrait;

    /**
     * @var object styles (concerning the marker style)
     */
    private $styles;

    /**
     * The ident of the object's property for the latitude.
     * @var string $latProperty
     */
    private $latProperty;

     /**
      * The ident of the object's property for the longitude.
      * @var string $latProperty
      */
    private $lonProperty;

    /**
     * @param string $p The latitude property ident.
     * @return MapWidget Chainable
     */
    public function setLatProperty($p)
    {
        $this->latProperty = $p;
        return $this;
    }

    /**
     * @return string
     */
    public function latProperty()
    {
        return $this->latProperty;
    }

     /**
      * @param string $p The longitude property ident.
      * @return MapWidget Chainable
      */
    public function setLonProperty($p)
    {
        $this->lonProperty = $p;
        return $this;
    }

    /**
     * @return string
     */
    public function lonProperty()
    {
        return $this->lonProperty;
    }

    /**
     * Get the latitude, from the object's lat property.
     * @return float
     */
    public function lat()
    {
        if (!$this->obj() || !$this->latProperty()) {
            return false;
        }
        $obj = $this->obj();
        return call_user_func([$obj, $this->latProperty()]);
    }

    /**
     * Get the longitude, from the object's lon property.
     * @return float
     */
    public function lon()
    {
        if (!$this->obj() || !$this->lonProperty()) {
            return false;
        }
        $obj = $this->obj();
        return call_user_func([$obj, $this->lonProperty()]);
    }

    /**
     * Get the widget's associated object.
     *
     * @return ModelInterface
     */
    public function obj()
    {
        $obj = null;
        $id = ( isset($GET['obj_id']) ? $GET['obj_id'] : 0 );
        $obj_type = ( isset($GET['obj_type']) ? $GET['obj_type'] : 0 );
        if ($id && $obj_type) {
            $obj = ModelFactory::instance()->get($obj_type, [
                'logger'=>$this->logger
            ]);
            $obj->load($id);
        }
        return $obj;
    }


    /**
     * Title and subtitle getter/setters
     *
     * @param mixed $subtitle The map widget subtitle.
     * @return MapWidget Chainable
     */
    public function setSubtitle($subtitle)
    {
        if ($subtitle === null) {
            $this->title = null;
        } else {
            $this->title = new TranslationString($subtitle);
        }
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $title The map widget title.
     * @return MapWidget Chainable
     */
    public function setTitle($title)
    {
        if ($title === null) {
            $this->title = null;
        } else {
            $this->title = new TranslationString($title);
        }
        return $this;
    }

    /**
     * @return TranslationString
     */
    public function title()
    {
        if ($this->title === null) {
            return new TranslationString('Actions');
        }
        return $this->title;
    }

    /**
     * @return string
     */
    public function widgetType()
    {
        return 'charcoal/admin/widget/map';
    }
}
