<?php

namespace Charcoal\Object;

/**
 * Defines a changeset of an object implementing {@see \Charcoal\Object\RevisionableInterface}.
 *
 * {@see \Charcoal\Object\ObjectRevision} for a basic implementation.
 */
interface ObjectRevisionInterface
{
    /**
     * @param string $targetType The object type (type-ident).
     * @return \Charcoal\Object\ObjectRevisionInterface Chainable
     */
    public function setTargetType($targetType);

    /**
     * @return string
     */
    public function targetType();

    /**
     * @param mixed $targetId The object ID.
     * @return \Charcoal\Object\ObjectRevisionInterface Chainable
     */
    public function setTargetId($targetId);

    /**
     * @return mixed
     */
    public function targetId();

    /**
     * @param integer $revNum The revision number.
     * @return \Charcoal\Object\ObjectRevisionInterface Chainable
     */
    public function setRevNum($revNum);

    /**
     * @return integer
     */
    public function revNum();

    /**
     * @param mixed $revTs The revision's timestamp.
     * @return \Charcoal\Object\ObjectRevisionInterface Chainable
     */
    public function setRevTs($revTs);

    /**
     * @return DateTime|null
     */
    public function revTs();

    /**
     * @param string $revUser The revision user ident.
     * @return \Charcoal\Object\ObjectRevisionInterface Chainable
     */
    public function setRevUser($revUser);

    /**
     * @return string
     */
    public function revUser();

    /**
     * @param array|string $data The previous revision data.
     * @return ObjectRevision
     */
    public function setDataPrev($data);

    /**
     * @return array
     */
    public function dataPrev();

    /**
     * @param array|string $data The current revision (object) data.
     * @return ObjectRevision
     */
    public function setDataObj($data);

    /**
     * @return array
     */
    public function dataObj();

     /**
      * @param array|string $data The data diff.
      * @return ObjectRevision
      */
    public function setDataDiff($data);

    /**
     * @return array
     */
    public function dataDiff();

    /**
     * Create a new revision from an object
     *
     * 1. Load the last revision
     * 2. Load the current item from DB
     * 3. Create diff from (1) and (2).
     *
     * @param RevisionableInterface $obj The object to create the revision from.
     * @return ObjectRevision Chainable
     */
    public function createFromObject(RevisionableInterface $obj);

    /**
     * @param array $dataPrev Optional. The previous revision data.
     * @param array $dataObj  Optional. The current revision (object) data.
     * @return array The diff data
     */
    public function createDiff(array $dataPrev, array $dataObj);

    /**
     * Recursive arrayDiff.
     *
     * @param array $array1 First array.
     * @param array $array2 Second array.
     * @return array The array diff.
     */
    public function recursiveDiff(array $array1, array $array2);

    /**
     * @param RevisionableInterface $obj The object  to load the last revision of.
     * @return ObjectRevision The last revision for the give object.
     */
    public function lastObjectRevision(RevisionableInterface $obj);

    /**
     * Retrieve a specific object revision, by revision number.
     *
     * @param RevisionableInterface $obj    Target object.
     * @param integer               $revNum The revision number to load.
     * @return ObjectRevision
     */
    public function objectRevisionNum(RevisionableInterface $obj, $revNum);
}
