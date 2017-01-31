<?php

namespace Charcoal\Admin\Ui;

use RuntimeException;

// From 'charcoal-translation'
use Charcoal\Translation\TranslationString;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\FormSidebarInterface;
use Charcoal\Admin\Ui\ObjectContainerInterface;

/**
 * Provides methods for building groups of linked/action-capable items.
 *
 * Can be used to build navigation bars, lists, and dropdowns.
 * This trait {@todo should be replaced} with {@see \Charcoal\Ui\Menu}.
 */
trait ActionContainerTrait
{
    /**
     * Keep track of priority pool.
     *
     * @var integer|null
     */
    protected $actionsPriority;

    /**
     * Parse the given UI actions.
     *
     * @param  array $actions  Actions to resolve.
     * @param  mixed $renderer Either a {@see ViewableInterface} or TRUE
     *     to determine if any renderables should be processed.
     * @return array List actions.
     */
    protected function parseActions(array $actions, $renderer = false)
    {
        $this->actionsPriority = $this->defaultActionPriority();

        $parsedActions = [];
        foreach ($actions as $ident => $action) {
            $ident  = $this->parseActionIdent($ident, $action);
            $action = $this->parseActionItem($action, $ident, $renderer);

            if (!isset($action['priority'])) {
                $action['priority'] = $this->actionsPriority++;
            }

            if (isset($parsedActions[$ident])) {
                $hasPriority = ($action['priority'] > $parsedActions[$ident]['priority']);
                if ($hasPriority || $action['isSubmittable']) {
                    $parsedActions[$ident] = array_replace($parsedActions[$ident], $action);
                } else {
                    $parsedActions[$ident] = array_replace($action, $parsedActions[$ident]);
                }
            } else {
                $parsedActions[$ident] = $action;
            }
        }

        usort($parsedActions, [ $this, 'sortActionsByPriority' ]);

        while (($first = reset($parsedActions)) && $first['isSeparator']) {
            array_shift($parsedActions);
        }

        while (($last = end($parsedActions)) && $last['isSeparator']) {
            array_pop($parsedActions);
        }

        return $parsedActions;
    }

    /**
     * Parse the given UI action identifier.
     *
     * @param  string $ident  The action identifier.
     * @param  mixed  $action The action structure.
     * @return string Resolved action identifier.
     */
    protected function parseActionIdent($ident, $action)
    {
        if (isset($action['ident'])) {
            $ident = $action['ident'];
        }

        return $ident;
    }

    /**
     * Parse the given UI action structure.
     *
     * @param  mixed  $action   The action structure.
     * @param  string $ident    The action identifier.
     * @param  mixed  $renderer Either a {@see ViewableInterface} or TRUE
     *     to determine if any renderables should be processed.
     * @return array Resolved action structure.
     */
    protected function parseActionItem($action, $ident, $renderer = false)
    {
        if ($action === '|') {
            $action = $this->defaultActionStruct();
            $action['isSeparator'] = true;
        } elseif (is_array($action)) {
            $buttonTypes = [ 'button', 'menu', 'reset', 'submit' ];
            // Normalize structure keys
            foreach ($action as $key => $val) {
                $attr = $this->getter($key);
                if ($key !== $attr) {
                    $action[$attr] = $val;
                    unset($action[$key]);
                }
            }

            if (!isset($action['ident'])) {
                $action['ident'] = $ident;
            }

            if (isset($action['buttonType'])) {
                if (!in_array($action['buttonType'], $buttonTypes)) {
                    $action['actionType'] = $action['buttonType'];
                    $action['buttonType'] = 'button';
                }
            }

            if (!isset($action['actionType'])) {
                $action['actionType'] = $this->resolveActionType($action);
            }

            if (isset($action['label']) && TranslationString::isTranslatable($action['label'])) {
                $action['label'] = new TranslationString($action['label']);
            } else {
                $action['label'] = ucwords(str_replace([ '.', '_' ], ' ', $action['ident']));

                $model = $this->getActionRenderer();
                if ($model) {
                    $meta  = $model->metadata();
                    $label = sprintf('%s_item', $action['ident']);
                    if (isset($meta['labels'][$label])) {
                        $action['label'] = new TranslationString($meta['labels'][$label]);
                    }
                }
            }

            if (isset($action['url']) && TranslationString::isTranslatable($action['url'])) {
                $action['url']      = new TranslationString($action['url']);
                $action['isText']   = false;
                $action['isLink']   = true;
                $action['isButton'] = false;
            } else {
                $action['url'] = '#';
            }

            if (isset($action['buttonType'])) {
                if (in_array($action['buttonType'], $buttonTypes)) {
                    $action['isLink']   = false;
                    $action['isButton'] = true;
                }

                if ($action['buttonType'] === 'submit') {
                    $action['isSubmittable'] = true;
                }
            }

            if (isset($action['actions']) && is_array($action['actions'])) {
                $action['actions']    = $this->parseActions($action['actions']);
                $action['hasActions'] = !!array_filter($action['actions'], function ($action) {
                    return $action['active'];
                });
            } else {
                $action['actions']    = [];
                $action['hasActions'] = false;
            }

            $action = array_replace($this->defaultActionStruct(), $action);

            if ($renderer) {
                $action = $this->parseActionRenderables($action, $renderer);
            }
        }

        return $action;
    }

    /**
     * Resolve the action's type.
     *
     * @param  mixed $action The action structure.
     * @return string
     */
    protected function resolveActionType($action)
    {
        switch ($action['ident']) {
            case 'create':
            case 'save':
            case 'submit':
            case 'update':
                return 'info';

            case 'edit':
                return 'primary';

            case 'reset':
                return 'warning';

            case 'delete':
                return 'danger';

            default:
                return 'default';
        }
    }

    /**
     * Fetch a viewable instance to process an action's renderables.
     *
     * @return ViewableInterface|null
     */
    protected function getActionRenderer()
    {
        if ($this instanceof FormSidebarInterface) {
            $obj = $this->form()->obj();
        }

        if ($this instanceof ObjectContainerInterface) {
            $obj = $this->obj();
        }

        if ($this instanceof CollectionContainerInterface) {
            $obj = isset($this->currentObj) ? $this->currentObj : $this->proto();
        }

        return $obj instanceof ViewableInterface ? $obj : null;
    }

    /**
     * Parse the given UI action's renderables (e.g., URLs).
     *
     * @param  mixed $action   The action structure.
     * @param  mixed $renderer Either a {@see ViewableInterface} or TRUE
     *     to determine if any renderables should be processed.
     * @throws RuntimeException If a renderer is unavailable.
     * @return array Resolved action structure.
     */
    protected function parseActionRenderables($action, $renderer)
    {
        if (!$renderer) {
            return $action;
        }

        if ($renderer === true) {
            $renderer = $this->getActionRenderer();
        }

        if (!$renderer instanceof ViewableInterface) {
            throw new RuntimeException('The widget has no renderer.');
        }

        if (isset($action['condition'])) {
            $action['active'] = $this->parseActionCondition($action['condition'], $action, $renderer);
            unset($action['condition']);
        }

        if (isset($action['url'])) {
            $action['url'] = $this->parseActionUrl($action['url'], $action, $renderer);
        }

        $action['cssClasses'] = $this->parseActionCssClasses($action['cssClasses'], $action, $renderer);
        $action['cssClasses'] = implode(' ', array_unique($action['cssClasses']));

        return $action;
    }

    /**
     * Parse the given UI action conditional check.
     *
     * @param  mixed $condition The action's conditional check.
     * @param  mixed $action    The action structure.
     * @param  mixed $renderer  The renderer.
     * @return array Resolved action structure.
     */
    protected function parseActionCondition($condition, $action = null, $renderer = null)
    {
        unset($action);

        if ($renderer === null) {
            $renderer = $this->getActionRenderer();
        }

        if (is_bool($condition)) {
            return $condition;
        } elseif (is_string($condition)) {
            if ($renderer && is_callable([ $renderer, $condition ])) {
                return $renderer->{$condition}();
            } elseif (is_callable([ $this, $condition ])) {
                return $this->{$condition}();
            } elseif (is_callable($condition)) {
                return $condition();
            } elseif ($renderer) {
                return $renderer->renderTemplate($condition);
            }
        }

        return $condition;
    }

    /**
     * Parse the given UI action URL.
     *
     * @param  string $url      The action's URL.
     * @param  mixed  $action   The action structure.
     * @param  mixed  $renderer The renderer.
     * @return array Resolved action structure.
     */
    protected function parseActionUrl($url, $action = null, $renderer = null)
    {
        unset($action);

        if ($renderer === null) {
            $renderer = $this->getActionRenderer();
        }

        if ($url instanceof TranslationString) {
            $url = $url->fallback();
        }

        if ($renderer === null) {
            /** @todo Shame! Force `{{ id }}` to use "obj_id" GET parameter… */
            $objId = filter_input(INPUT_GET, 'obj_id', FILTER_SANITIZE_STRING);
            if ($objId) {
                $url = preg_replace('~\{\{\s*(obj_)?id\s*\}\}~', $objId, $url);
            }

            /** @todo Shame! Force `{{ type }}` to use "obj_type" GET parameter… */
            $objType = filter_input(INPUT_GET, 'obj_type', FILTER_SANITIZE_STRING);
            if ($objType) {
                $url = preg_replace('~\{\{\s*(obj_)?type\s*\}\}~', $objType, $url);
            }

            return $url;
        }

        $url = $renderer->renderTemplate($url);

        if ($url && strpos($url, ':') === false && !in_array($url[0], [ '/', '#', '?' ])) {
            $url = $this->adminUrl().$url;
        }

        return $url;
    }

    /**
     * Parse the given UI action CSS classes.
     *
     * @param  mixed $classes  The action's CSS classes.
     * @param  mixed $action   The action structure.
     * @param  mixed $renderer The renderer.
     * @return array
     */
    protected function parseActionCssClasses($classes, $action = null, $renderer = null)
    {
        if ($renderer === null) {
            $renderer = $this->getActionRenderer();
        }

        if (is_string($classes)) {
            $classes = explode(' ', $classes);
        } elseif (!is_array($classes)) {
            $classes = [];
        }

        $classes[] = 'btn';
        $classes[] = 'btn-'.$action['actionType'];
        $classes[] = $this->jsActionPrefix().'-'.$action['ident'];

        return $classes;
    }

    /**
     * Retrieve the default action structure.
     *
     * @return array
     */
    protected function defaultActionStruct()
    {
        return [
            'ident'         => null,
            'priority'      => null,
            'active'        => true,
            'empty'         => false,
            'label'         => null,
            'showLabel'     => true,
            'icon'          => null,
            'glyphicon'     => false,
            'url'           => null,
            'target'        => null,
            'isText'        => false,
            'isLink'        => false,
            'isButton'      => true,
            'isHeader'      => false,
            'isSubmittable' => false,
            'isSeparator'   => false,
            'cssClasses'    => null,
            'actionType'    => 'default',
            'buttonType'    => 'button',
            'splitButton'   => false,
            'hasActions'    => false,
            'actions'       => [],
        ];
    }

    /**
     * Retrieve the default sorting priority for actions.
     *
     * @return integer
     */
    protected function defaultActionPriority()
    {
        return defined('static::DEFAULT_ACTION_PRIORITY') ? static::DEFAULT_ACTION_PRIORITY : 10;
    }

    /**
     * To be called with uasort()
     *
     * @param  array $a First action object to sort.
     * @param  array $b Second action object to sort.
     * @return integer
     */
    protected static function sortActionsByPriority(array $a, array $b)
    {
        $a = isset($a['priority']) ? $a['priority'] : 0;
        $b = isset($b['priority']) ? $b['priority'] : 0;

        return ($a < $b) ? (-1) : 1;
    }

    /**
     * @return string
     */
    abstract public function jsActionPrefix();
}
