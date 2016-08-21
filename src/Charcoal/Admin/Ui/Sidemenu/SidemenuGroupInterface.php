<?php

namespace Charcoal\Admin\Ui\Sidemenu;

use Charcoal\Admin\Widget\SidemenuWidgetInterface;

/**
 * Defines an admin sidemenu group
 */
interface SidemenuGroupInterface
{
    /**
     * Set the sidemenu widget.
     *
     * @param SidemenuWidgetInterface $sidemenu The related sidemenu widget.
     * @return SidemenuGroupInterface Chainable
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
     * @param string $ident Sidemenu group identifier.
     * @return UiGroupingInterface Chainable
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

    /**
     * Set the group's priority or sorting index.
     *
     * @param integer $priority An index, for sorting.
     * @return UiGroupingInterface Chainable
     */
    public function setPriority($priority);

    /**
     * Retrieve the group's priority or sorting index.
     *
     * @return integer
     */
    public function priority();
}
