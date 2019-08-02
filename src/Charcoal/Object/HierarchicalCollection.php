<?php

namespace Charcoal\Object;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\Collection as CharcoalCollection;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalInterface;

/**
 * A Hierarchical Model Collection
 *
 * Sorts and flattens a collection of hierarchically-interrelated models.
 *
 * This class is not recommended. Currently, only designed and used by
 * {@see \Charcoal\Support\Property\HierarchicalObjectProperty} and
 * {@see \Charcoal\Support\Admin\Widget\HierarchicalTableWidget}.
 */
class HierarchicalCollection extends CharcoalCollection
{
    /**
     * The current page (slice).
     *
     * @var integer
     */
    protected $page = 0;

    /**
     * The number of objects per page (slice).
     *
     * @var integer
     */
    protected $numPerPage = 0;

    /**
     * Create a new hierarchically-sorted collection.
     *
     * @param  array|\Traversable|null $objs Array of objects to pre-populate this collection.
     * @param  boolean                 $sort Whether to sort the collection immediately.
     * @return void
     */
    public function __construct($objs = [], $sort = true)
    {
        if ($objs) {
            $this->merge($objs);

            if ($sort) {
                $this->sortTree();
            }
        }
    }

    /**
     * Sort the hierarchical collection of objects.
     *
     * @return self
     */
    public function sortTree()
    {
        $level   = 0;
        $count   = 0;
        $pageNum = $this->page();
        $perPage = $this->numPerPage();

        $sortedObjects = [];
        $rootObjects   = [];
        $childObjects  = [];

        foreach ($this->objects as $object) {
            // Repair bad hierarchy.
            if ($object->hasMaster() && $object->getMaster()->id() === $object->id()) {
                $object->setMaster(0);
                $object->update([ 'master' ]);
            }

            if ($object->hasMaster()) {
                $childObjects[$object->getMaster()->id()][] = $object;
            } else {
                $rootObjects[] = $object;
            }
        }

        if (empty($rootObjects) && !empty($childObjects)) {
            foreach ($childObjects as $parentId => $children) {
                $parentObj = $children[0]->master();
                $parentObj->auxiliary = true;

                $rootObjects[] = $parentObj;
            }
        }

        $this->objects = &$rootObjects;

        if ($perPage < 1) {
            foreach ($this->objects as $object) {
                $object->level = $level;
                $sortedObjects[$object->id()] = $object;

                $count++;

                if (isset($childObjects[$object->id()])) {
                    $this->sortDescendantObjects(
                        $object,
                        $childObjects,
                        $count,
                        ($level + 1),
                        $sortedObjects
                    );
                }
            }
        } else {
            $start = (( $pageNum - 1 ) * $perPage);
            $end   = ($start + $perPage);

            foreach ($this->objects as $object) {
                if ($count >= $end) {
                    break;
                }

                if ($count >= $start) {
                    $object->level = $level;
                    $sortedObjects[$object->id()] = $object;
                }

                $count++;

                if (isset($childObjects[$object->id()])) {
                    $this->sortDescendantObjects(
                        $object,
                        $childObjects,
                        $count,
                        ($level + 1),
                        $sortedObjects
                    );
                }
            }

            // If we are on the last page, display orphaned descendants.
            if ($childObjects && $count < $end) {
                foreach ($childObjects as $orphans) {
                    foreach ($orphans as $descendants) {
                        if ($count >= $end) {
                            break;
                        }

                        if ($count >= $start) {
                            $descendants->level = 0;
                            $sortedObjects[$descendants->id()] = $descendants;
                        }

                        $count++;
                    }
                }
            }
        }

        $this->objects = $sortedObjects;

        return $this;
    }

    /**
     * Given an object, display the nested hierarchy of descendants.
     *
     * @param  HierarchicalInterface   $parentObj     The parent object from which to append its
     *     descendants for display.
     * @param  HierarchicalInterface[] $childObjects  The list of descendants by parent object ID.
     *     Passed by reference.
     * @param  integer                 $count         The current count of objects to display,
     *     for pagination. Passed by reference.
     * @param  integer                 $level         The level directly below the $parentObj.
     * @param  HierarchicalInterface[] $sortedObjects The list of objects to be displayed.
     *     Passed by reference.
     * @return void
     */
    private function sortDescendantObjects(
        HierarchicalInterface $parentObj,
        array &$childObjects,
        &$count,
        $level,
        array &$sortedObjects
    ) {
        $pageNum = $this->page();
        $perPage = $this->numPerPage();

        if ($perPage < 1) {
            foreach ($childObjects[$parentObj->id()] as $object) {
                if ($count === 0 && $object->hasMaster()) {
                    $myParents = [];
                    $myParent  = $object->master();
                    while ($myParent) {
                        $myParents[] = $myParent;

                        if (!$myParent->hasMaster()) {
                            break;
                        }

                        $myParent = $myParent->master();
                    }

                    $numParents = count($myParents);
                    while ($myParent = array_pop($myParents)) {
                        $myParent->level = ($level - $numParents);
                        $sortedObjects[$myParent->id()] = $myParent;
                        $numParents--;
                    }
                }

                $object->level = $level;
                $sortedObjects[$object->id()] = $object;

                $count++;

                if (isset($childObjects[$object->id()])) {
                    $this->sortDescendantObjects(
                        $object,
                        $childObjects,
                        $count,
                        ($level + 1),
                        $sortedObjects
                    );
                }
            }
        } else {
            $start = (( $pageNum - 1 ) * $perPage);
            $end   = ($start + $perPage);

            foreach ($childObjects[$parentObj->id()] as $object) {
                if ($count >= $end) {
                    break;
                }

                // If the page starts in a subtree, print the parents.
                if ($count === $start && $object->hasMaster()) {
                    $myParents = [];
                    $myParent  = $object->master();
                    while ($myParent) {
                        $myParents[] = $myParent;

                        if (!$myParent->hasMaster()) {
                            break;
                        }

                        $myParent = $myParent->master();
                    }

                    $numParents = count($myParents);
                    while ($myParent = array_pop($myParents)) {
                        $myParent->level = ($level - $numParents);
                        $sortedObjects[$myParent->id()] = $myParent;
                        $numParents--;
                    }
                }

                if ($count >= $start) {
                    $object->level = $level;
                    $sortedObjects[$object->id()] = $object;
                }

                $count++;

                if (isset($childObjects[$object->id()])) {
                    $this->sortDescendantObjects(
                        $object,
                        $childObjects,
                        $count,
                        ($level + 1),
                        $sortedObjects
                    );
                }
            }
        }

        // Required in order to keep track of orphans
        unset($childObjects[$parentObj->id()]);
    }

    /**
     * @param  integer $page The current page. Start at 0.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setPage($page)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'Page number needs to be numeric.'
            );
        }

        $page = (int)$page;
        if ($page < 0) {
            throw new InvalidArgumentException(
                'Page number needs to be >= 0.'
            );
        }

        $this->page = $page;

        return $this;
    }

    /**
     * @return integer
     */
    public function page()
    {
        return $this->page;
    }

    /**
     * @param  integer $num The number of results to retrieve, per page.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setNumPerPage($num)
    {
        if (!is_numeric($num)) {
            throw new InvalidArgumentException(
                'Num-per-page needs to be numeric.'
            );
        }

        $num = (int)$num;

        if ($num < 0) {
            throw new InvalidArgumentException(
                'Num-per-page needs to be >= 0.'
            );
        }

        $this->numPerPage = $num;

        return $this;
    }

    /**
     * @return integer
     */
    public function numPerPage()
    {
        return $this->numPerPage;
    }

    /**
     * Determine if the given value is acceptable for the collection.
     *
     * @param  mixed $value The value being vetted.
     * @return boolean
     */
    public function isAcceptable($value)
    {
        return ($value instanceof HierarchicalInterface);
    }
}
