<?php

namespace Charcoal\Admin\Ui\Sidemenu;

// From 'charcoal-ui'
use Charcoal\Ui\PrioritizableInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\SidemenuWidgetInterface;

/**
 * Defines an admin sidemenu group
 */
interface SidemenuGroupInterface extends
    PrioritizableInterface
{
    /**
     * Set the sidemenu widget.
     *
     * @param  SidemenuWidgetInterface $sidemenu The related sidemenu widget.
     * @return self
     */
    public function setSidemenu(SidemenuWidgetInterface $sidemenu);

    /**
     * Retrieve the sidemenu widget.
     *
     * @return SidemenuWidgetInterface
     */
    public function sidemenu();

    /**
     * Set the identifier of the group.
     *
     * @param  string $ident Sidemenu group identifier.
     * @return self
     */
    public function setIdent($ident);

    /**
     * Retrieve the idenfitier of the group.
     *
     * @return string
     */
    public function ident();

    /**
     * Set whether the group is active or not.
     *
     * @param  boolean $active The active flag.
     * @return self
     */
    public function setActive($active);

    /**
     * Determine if the group is active or not.
     *
     * @return boolean
     */
    public function active();
}
