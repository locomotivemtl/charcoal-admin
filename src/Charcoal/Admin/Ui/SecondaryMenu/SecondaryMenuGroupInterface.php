<?php

namespace Charcoal\Admin\Ui\SecondaryMenu;

// From 'charcoal-ui'
use Charcoal\Ui\PrioritizableInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\SecondaryMenuWidgetInterface;

/**
 * Defines an admin secondary menu group
 */
interface SecondaryMenuGroupInterface extends
    PrioritizableInterface
{
    /**
     * Set the secondary menu widget.
     *
     * @param  SecondaryMenuWidgetInterface $menu The related secondary menu widget.
     * @return self
     */
    public function setSecondaryMenu(SecondaryMenuWidgetInterface $menu);

    /**
     * Retrieve the secondary menu widget.
     *
     * @return SecondaryMenuWidgetInterface
     */
    public function secondaryMenu();

    /**
     * Set the identifier of the group.
     *
     * @param  string $ident Secondary menu group identifier.
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
