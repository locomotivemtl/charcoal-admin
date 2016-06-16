<?php

namespace Charcoal\Admin\Widget;

use \Charcoal\Factory\FactoryInterface;

use \Charcoal\Loader\CollectionLoader;

use \Charcoal\Admin\AdminWidget;

use \Charcoal\Presenter\Presenter;

/**
 *
 */
class CollectionMapWidget extends AdminWidget
{
    private $mapObjects;
    private $objProto;

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

    public $infoboxTemplate = '';

    /**
     * @return \Alert\User
     */
    private function objProto()
    {
        if ($this->objProto === null) {
            $this->objProto = $this->modelFactory()->create($this->{'obj_type'});
        }
        return $this->objProto;
    }

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

    public function setInfoboxTemplate($template)
    {
        $this->infoboxTemplate = $template;
        return $this;
    }

    public function infoboxTemplate()
    {
        return $this->infoboxTemplate;
    }

    /**
     * Return all the objs with geographical information
     *
     * @return Collection
     */
    public function mapObjects()
    {
        if ($this->mapObjects === null) {
            $loader = new CollectionLoader([
                'logger'    => $this->logger,
                'factory'   => $this->modelFactory()
            ]);
            $loader->setModel($this->objProto());

            $infoboxTemplate = $this->infoboxTemplate();
            $loader->setCallback(function($obj) use ($infoboxTemplate) {
                $obj->infoboxTemplate = $infoboxTemplate;
            });

            $this->mapObjects = $loader->load();
        }

        foreach($this->mapObjects as $obj) {
            $GLOBALS['widget_template'] = $obj->infoboxTemplate;
            yield $obj;
        }
    }

    public function showInfobox()
    {
        return ($this->infoboxTemplate != '');
    }
}
