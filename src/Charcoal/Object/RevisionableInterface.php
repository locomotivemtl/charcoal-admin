<?php

namespace Charcoal\Object;

/**
 *
 */
interface RevisionableInterface
{
    /**
     * @param boolean $enabled The (revision) enabled flag.
     * @return RevisionableInterface Chainable
     */
    public function setRevisionEnabled($enabled);

    /**
     * @return boolean
     */
    public function getRevisionEnabled();

    /**
     * Create a revision object.
     *
     * @return \Charcoal\Object\ObjectRevisionInterface
     */
    public function createRevisionObject();

    /**
     * Retrieve the class name of the object revision model.
     *
     * @return string
     */
    public function objectRevisionClass();

    /**
     * @return \Charcoal\Object\ObjectRevisionInterface
     */
    public function generateRevision();

    /**
     * @return \Charcoal\Object\ObjectRevisionInterface
     */
    public function latestRevision();

    /**
     * @param integer $revNum The revision number to retrieve.
     * @return \Charcoal\Object\ObjectRevisionInterface
     * @see \Charcoal\Object\ObejctRevision::objectRevisionNum
     */
    public function revisionNum($revNum);

    /**
     * @return array
     */
    public function allRevisions();

    /**
     * @param integer $revNum The revision number to revert to.
     * @return boolean Success / Failure.
     */
    public function revertToRevision($revNum);
}
