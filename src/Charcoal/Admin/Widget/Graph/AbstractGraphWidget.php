<?php

namespace Charcoal\Admin\Widget\Graph;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Widget\Graph\GraphWidgetInterface;

use Charcoal\Admin\Ui\ActionContainerTrait;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;

/**
 * Base Graph widget
 */
abstract class AbstractGraphWidget extends AdminWidget implements 
    GraphWidgetInterface
{
    use ActionContainerTrait;

    /**
     * Default sorting priority for an action.
     *
     * @const integer
     */
    const DEFAULT_ACTION_PRIORITY = 10;

    /**
     * Store the list actions.
     *
     * @var array|null
     */
    protected $showGraphActions = true;

    /**
     * Store the list actions.
     *
     * @var array|null
     */
    protected $graphActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    protected $defaultGraphActions;

    /**
     * Keep track if list actions are finalized.
     *
     * @var boolean
     */
    protected $parsedGraphActions = false;

    /**
     * @var mixed $height
     */
    protected $height = 400;

    /**
     * @var array $colors
     */
    protected $colors;

    /**
     * @param mixed $height The graph height (for CSS).
     * @return GraphWidgetInterface Chainable
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function height()
    {
        return $this->height;
    }

    /**
     * @param string[] $colors The graph colors.
     * @return GraphWidgetInterface Chainable
     */
    public function setColors(array $colors)
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * @return string[]
     */
    public function colors()
    {
        if ($this->colors === null || empty($this->colors)) {
            $this->colors = $this->defaultColors();
        }

        return $this->colors;
    }

    /**
     * @todo Read from widget metadata
     * @return string[]
     */
    public function defaultColors()
    {
        return [
            '#ED5564',
            '#337AB7',
            '#DA70D6',
            '#32CD32',
            '#6495ED',
            '#FF69B4',
            '#BA55D3',
            '#CD5C5C',
            '#FFA500',
            '#40E0D0',
            '#1E90FF',
            '#FF6347',
            '#7B68EE',
            '#00FA9A',
            '#FFD700',
            '#6B8E23',
            '#FF00FF',
            '#3CB371',
            '#B8860B',
            '#30E0E0'
        ];
    }

    /**
     * @return string JSONified categories structure.
     */
    public function seriesJson()
    {
        return json_encode($this->series());
    }

    /**
     * @return string JSONified categories structure.
     */
    public function categoriesJson()
    {
        return json_encode($this->categories());
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        return [
            'list_actions'     => $this->graphActions(),
            'colors'  => $this->colors(),
            'options' => [
                'xAxis' => empty($this->categories()) ? null : [
                    [
                        'type' => 'category',
                        'data' => $this->categories()
                    ]
                ],
                'yAxis' => empty($this->categories()) ? null : [
                    [
                        'type'      => 'value',
                        'splitArea' => ['show' => true]
                    ]
                ],
                'series' => $this->series()
            ]
        ];
    }

    /**
     * @return array Categories structure.
     */
    abstract public function categories();

    /**
     * @return array Series structure.
     */
    abstract public function series();

    /**
     * Determine if the table's collection actions should be shown.
     *
     * @return boolean
     */
    public function showGraphActions()
    {
        if ($this->showGraphActions === false) {
            return false;
        } else {
            return count($this->graphActions());
        }
    }

    /**
     * Retrieve the table's collection actions.
     *
     * @return array
     */
    public function graphActions()
    {
        if ($this->graphActions === null) {
            $collectionConfig = $this->collectionConfig();
            if (isset($collectionConfig['list_actions'])) {
                $actions = $collectionConfig['list_actions'];
            } else {
                $actions = [];
            }
            $this->setGraphActions($actions);
        }

        if ($this->parsedGraphActions === false) {
            $this->parsedGraphActions = true;
            $this->graphActions = $this->createGraphActions($this->graphActions);
        }

        return $this->graphActions;
    }

    /**
     * Set the table's collection actions.
     *
     * @param  array $actions One or more actions.
     * @return TableWidget Chainable.
     */
    protected function setGraphActions(array $actions)
    {
        $this->parsedGraphActions = false;

        $this->graphActions = $this->mergeActions($this->defaultGraphActions(), $actions);

        return $this;
    }

    /**
     * Retrieve the table's default collection actions.
     *
     * @return array
     */
    protected function defaultGraphActions()
    {
        if ($this->defaultGraphActions === null) {
            $this->defaultGraphActions = [];
        }

        return $this->defaultGraphActions;
    }

    /**
     * Build the table collection actions.
     *
     * List actions should come from the collection settings defined by the "collection_ident".
     * It is still possible to completly override those externally by setting the "list_actions"
     * with the {@see self::setGraphActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array List actions.
     */
    protected function createGraphActions(array $actions)
    {
        $this->actionsPriority = $this->defaultActionPriority();

        $graphActions = $this->parseAsGraphActions($actions);

        return $graphActions;
    }

    /**
     * Parse the given actions as collection actions.
     *
     * @param  array $actions Actions to resolve.
     * @return array
     */
    protected function parseAsGraphActions(array $actions)
    {
        $graphActions = [];
        foreach ($actions as $ident => $action) {
            $ident  = $this->parseActionIdent($ident, $action);
            $action = $this->parseActionItem($action, $ident, true);

            if (!isset($action['priority'])) {
                $action['priority'] = $this->actionsPriority++;
            }

            $action['empty'] = (isset($action['empty']) ? boolval($action['empty']) : false);

            if (is_array($action['actions'])) {
                $action['actions']    = $this->parseAsGraphActions($action['actions']);
                $action['hasActions'] = !!array_filter($action['actions'], function ($action) {
                    return $action['active'];
                });
            }

            if (isset($graphActions[$ident])) {
                $hasPriority = ($action['priority'] > $graphActions[$ident]['priority']);
                if ($hasPriority || $action['isSubmittable']) {
                    $graphActions[$ident] = array_replace($graphActions[$ident], $action);
                } else {
                    $graphActions[$ident] = array_replace($action, $graphActions[$ident]);
                }
            } else {
                $graphActions[$ident] = $action;
            }
        }

        usort($graphActions, [ $this, 'sortActionsByPriority' ]);

        while (($first = reset($graphActions)) && $first['isSeparator']) {
            array_shift($graphActions);
        }

        while (($last = end($graphActions)) && $last['isSeparator']) {
            array_pop($graphActions);
        }

        return $graphActions;
    }

    /**
     * @return string
     */
    public function jsActionPrefix()
    {
        return 'js';
    }
}
