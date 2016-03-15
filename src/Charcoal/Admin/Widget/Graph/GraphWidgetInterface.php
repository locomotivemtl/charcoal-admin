<?php

namespace Charcoal\Admin\Widget\Graph;

/**
 * Graph Widget Interface
 *
 *
 */
interface GraphWidgetInterface
{
     /**
      * @param mixed $height
      * @return GraphWidgetInterface Chainable
      */
    public function setHeight($height);

    /**
     * @return mixed
     */
    public function height();

    /**
     * @param string[] $colors
     * @return GraphWidgetInterface Chainable
     */
    public function setColors(array $colors);

    /**
     *
     */
    public function colors();

    /**
     * @todo Read from widget metadata
     */
    public function defaultColors();

    /**
     * @return array Categories structure.
     */
    public function categories();

    /**
     * @return string JSONified categories structure.
     */
    public function categoriesJson();

    /**
     * @return array Series structure.
     */
    public function series();

     /**
      * @return string JSONified series structure.
      */
    public function seriesJson();
}
