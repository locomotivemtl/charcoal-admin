<?php

namespace Charcoal\Admin\Support;

/**
 * A collection of static sorter functions.
 */
class Sorter
{
    /**
     * To be called with {@see uasort()}.
     *
     * @param  $a Sortable action A.
     * @param  $b Sortable action B.
     * @return integer
     */
    public static function sortByPriority($a, $b)
    {
        $a = isset($a['priority']) ? $a['priority'] : 0;
        $b = isset($b['priority']) ? $b['priority'] : 0;

        if ($a === $b) {
            return 0;
        }
        return ($a < $b) ? (-1) : 1;
    }
}
