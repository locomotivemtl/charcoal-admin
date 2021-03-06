<?php

namespace Charcoal\Admin\Widget\Graph;

/**
 * Graph Widget Interface
 */
interface GraphWidgetInterface
{
    /**
     * @param mixed $height Graph default height (for CSS).
     * @return GraphWidgetInterface Chainable
     */
    public function setHeight($height);

    /**
     * @return mixed
     */
    public function height();

    /**
     * @param string[] $colors The graph colors (hexadecimal).
     * @return GraphWidgetInterface Chainable
     */
    public function setColors(array $colors);

    /**
     * @return string[]
     */
    public function colors();

    /**
     * @return string[]
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
