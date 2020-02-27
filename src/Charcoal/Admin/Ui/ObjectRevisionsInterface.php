<?php

namespace Charcoal\Admin\Ui;

/**
 * Defines awareness of a object revisions.
 *
 * Implementation, as trait, provided by {@see \Charcoal\Admin\Ui\ObjectRevisionsTrait}.
 */
interface ObjectRevisionsInterface
{
    /**
     * @return \Charcoal\Object\ObjectRevisionInterface[]
     */
    public function objectRevisions();
}
