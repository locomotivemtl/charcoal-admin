<?php

namespace Charcoal\Admin\Decorator;

use Charcoal\App\Template\WidgetInterface;

/**
 * Decorate a widget with Grid Stack features.
 * { @link https://github.com/gridstack/gridstack.js }
 * { @see Charcoal\Admin\Widget\GridStackDashboardWidget }
 *
 * Grid Stack Widget Decorator
 */
class GridStackWidgetDecorator
{
    const GS_WIDTH = 4;
    const GS_HEIGHT = 4;

    /**
     * @var array
     */
    protected $gridStack = [];

    /**
     * @var WidgetInterface
     */
    protected $widget;

    /**
     * GridStackWidgetDecorator constructor.
     * @param WidgetInterface $widget The widget to decorate.
     */
    public function __construct(WidgetInterface $widget)
    {
        $this->widget = $widget;
    }

    /**
     * @return array
     */
    public function gridStack()
    {
        if ($this->gridStack) {
            return $this->gridStack;
        }

        $gridStack = $this->defaultGridStack();

        if (isset($this->widget['grid_stack'])) {
            $gridStack = array_replace_recursive(
                $gridStack,
                $this->widget['grid_stack']
            );
        }

        $this->gridStack = $gridStack;

        return $this->gridStack;
    }

    /**
     * The default Grid Stack dataset.
     *
     * @return array
     */
    private function defaultGridStack()
    {
        return [
            'width' => self::GS_WIDTH,
            'height' => self::GS_HEIGHT
        ];
    }
}
