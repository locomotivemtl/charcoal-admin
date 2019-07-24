<?php

namespace Charcoal\Object;

/**
 *
 */
interface HierarchicalInterface
{
    /**
     * Determine if this object has a direct parent.
     *
     * @return boolean
     */
    public function hasMaster();

    /**
     * Determine if this object is the head (top-level) of its hierarchy.
     *
     * Top-level objects do not have a parent (master).
     *
     * @return boolean
     */
    public function isTopLevel();

    /**
     * Determine if this object is the tail (last-level) of its hierarchy.
     *
     * Last-level objects do not have a children.
     *
     * @return boolean
     */
    public function isLastLevel();

    /**
     * Retrieve this object's position (level) in its hierarchy.
     *
     * Starts at "1" (top-level).
     *
     * @return integer
     */
    public function hierarchyLevel();

    /**
     * Retrieve this object's immediate parent.
     *
     * @return HierarchicalInterface|null
     */
    public function getMaster();

    /**
     * Retrieve the top-level ancestor of this object.
     *
     * @return HierarchicalInterface|null
     */
    public function toplevelMaster();

    /**
     * Determine if this object has any ancestors.
     *
     * @return boolean
     */
    public function hasParents();

    /**
     * Retrieve this object's ancestors (from immediate parent to top-level).
     *
     * @return array
     */
    public function hierarchy();

    /**
     * Retrieve this object's ancestors, inverted from top-level to immediate.
     *
     * @return array
     */
    public function invertedHierarchy();

    /**
     * Determine if the object is the parent of the given object.
     *
     * @param mixed $child The child (or ID) to match against.
     * @return boolean
     */
    public function isMasterOf($child);

    /**
     * Determine if the object is a parent/ancestor of the given object.
     *
     * @param mixed $child The child (or ID) to match against.
     * @return boolean
     */
    public function recursiveIsMasterOf($child);

    /**
     * Get wether the object has any children at all
     * @return boolean
     */
    public function hasChildren();

    /**
     * Get the number of chidlren directly under this object.
     * @return integer
     */
    public function numChildren();

    /**
     * Get the total number of children in the entire hierarchy.
     * This method counts all children and sub-children, unlike `numChildren()` which only count 1 level.
     * @return integer
     */
    public function recursiveNumChildren();


    /**
     * Get the children directly under this object.
     * @return array
     */
    public function children();

    /**
     * @param mixed $master The master object (or ident) to check against.
     * @return boolean The master object (or ident) to check against.
     */
    public function isChildOf($master);

    /**
     * @param mixed $master The master object (or ident) to check against.
     * @return boolean
     */
    public function recursiveIsChildOf($master);

    /**
     * @return boolean
     */
    public function hasSiblings();

    /**
     * @return integer
     */
    public function numSiblings();

    /**
     * Get all the objects on the same level as this one.
     * @return array
     */
    public function siblings();

    /**
     * @param mixed $sibling The sibling object (or ident) to check against.
     * @return boolean
     */
    public function isSiblingOf($sibling);
}
