<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Translation\TranslationString;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Template\TemplateViewController as TemplateViewController;

// From `charcoal-base`
use \Charcoal\Model\ModelFactory;


use \Charcoal\Admin\Ui\FormGroupInterface;
use \Charcoal\Admin\Ui\FormGroupTrait;

class MapWidget extends AdminWidget implements FormGroupInterface
{
    use FormGroupTrait;

    /**
    * @var object styles (concerning the marker style)
    */
    private $styles;

    private $latProperty;
    private $lonProperty;

    public function setLatProperty($p)
    {
        $this->latProperty = $p;
    }

    public function latProperty()
    {
        return $this->latProperty;
    }
    public function setLonProperty($p)
    {
        $this->lonProperty = $p;
    }
    public function lonProperty()
    {
        return $this->lonProperty;
    }

    public function lat()
    {
        if (!$this->obj() || !$this->latProperty()) {
            return false;
        }
        $obj = $this->obj();
        return call_user_func([$obj, $this->latProperty()]);
    }

    public function lon()
    {
        if (!$this->obj() || !$this->lonProperty()) {
            return false;
        }
        $obj = $this->obj();
        return call_user_func([$obj, $this->lonProperty()]);
    }

    /**
    * Styles
    */
    public function styles()
    {

    }

    public function setStyles($styles)
    {
        if (!$styles) {
            return $this;
        }

        return $this;
    }

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
    * @param mixed $subtitle l10n object OR string
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
    * @param mixed $title
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
