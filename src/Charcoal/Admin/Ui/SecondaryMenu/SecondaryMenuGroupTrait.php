<?php

namespace Charcoal\Admin\Ui\SecondaryMenu;

use ArrayIterator;
use InvalidArgumentException;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\SecondaryMenuWidgetInterface;

/**
 * Provides an implementation of {@see \Charcoal\Admin\Ui\SecondaryMenu\SecondaryMenuGroupInterface}.
 */
trait SecondaryMenuGroupTrait
{
    /**
     * Store a reference to the parent secondary menu widget.
     *
     * @var SecondaryMenuWidgetInterface
     */
    protected $secondaryMenu;

    /**
     * The secondary menu group ID.
     *
     * @var string
     */
    protected $groupId;

    /**
     * The group's display type.
     *
     * @var string
     */
    protected $displayType;

    /**
     * Whether the item is selected or not.
     *
     * @var boolean
     */
    protected $isSelected = false;

    /**
     * Whether the group is collapsible or not.
     *
     * @var boolean
     */
    private $collapsible = false;

    /**
     * Whether the group is collapsed or not.
     *
     * @var boolean
     */
    private $collapsed;

    /**
     * Whether the group has siblings or not.
     *
     * @var boolean
     */
    private $parented = false;

    /**
     * The group's links.
     *
     * @var array
     */
    protected $links;

    /**
     * The group's identifier.
     *
     * @var string
     */
    private $ident;

    /**
     * Whether the group is active.
     *
     * @var boolean
     */
    private $active = true;

    /**
     * Set the secondary menu widget.
     *
     * @param  SecondaryMenuWidgetInterface $menu The related secondary menu widget.
     * @return self
     */
    public function setSecondaryMenu(SecondaryMenuWidgetInterface $menu)
    {
        $this->secondaryMenu = $menu;

        return $this;
    }

    /**
     * Retrieve the secondary menu widget.
     *
     * @return SecondaryMenuWidgetInterface
     */
    public function secondaryMenu()
    {
        return $this->secondaryMenu;
    }

    /**
     * Set the group ID.
     *
     * @param  string $id The group ID.
     * @throws InvalidArgumentException If the group ID argument is not a string.
     * @return self
     */
    public function setGroupId($id)
    {
        if (!is_string($id)) {
            throw new InvalidArgumentException(
                'Group ID must be a string'
            );
        }

        $this->groupId = $id;

        return $this;
    }

    /**
     * Retrieve the group ID.
     *
     * @return string
     */
    public function groupId()
    {
        if ($this->groupId === null) {
            $this->groupId = uniqid('secondary_menu_group_');
        }

        return $this->groupId;
    }

    /**
     * Set the display type of the group.
     *
     * @param  mixed $type The display type.
     * @throws InvalidArgumentException If the display type is invalid.
     * @return self
     */
    public function setDisplayType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('The display type must be a string.');
        }

        $this->displayType = $type;

        return $this;
    }

    /**
     * Retrieve the display type of the group.
     *
     * @return string|null
     */
    public function displayType()
    {
        return $this->displayType;
    }

    /**
     * Set the secondary menu links.
     *
     * @param  array $links A collection of link objects.
     * @return self
     */
    public function setLinks(array $links)
    {
        $this->links = new ArrayIterator();

        foreach ($links as $linkIdent => $link) {
            $this->addLink($linkIdent, $link);
        }

        return $this;
    }

    /**
     * Set the secondary menu links.
     *
     * @param  string       $linkIdent The link identifier.
     * @param  array|object $link      The link object or structure.
     * @throws InvalidArgumentException If the link is invalid.
     * @return self
     */
    public function addLink($linkIdent, $link)
    {
        if (!is_string($linkIdent) && !is_numeric($linkIdent)) {
            throw new InvalidArgumentException(
                'Link identifier must be a string or '
            );
        }

        if (is_array($link)) {
            $active = true;
            $name = null;
            $url = null;
            $permissions = [];

            if (isset($link['active'])) {
                $active = !!$link['active'];
            }

            if (isset($link['name'])) {
                $name = $this->translator()->translation($link['name']);
            }

            if (isset($link['url'])) {
                $url = $this->translator()->translation($link['url']);
            }

            if (isset($link['required_acl_permissions'])) {
                $permissions = $link['required_acl_permissions'];
            }

            if ($name === null && $url === null) {
                return $this;
            }

            $isSelected = $this->secondaryMenu()->isCurrentItem([ $linkIdent, (string)$url ]);

            if ($isSelected) {
                $this->isSelected(true);
            }

            $this->links[$linkIdent] = [
                'active'   => $active,
                'name'     => $name,
                'url'      => $url,
                'selected' => $isSelected,
                'required_acl_permissions' => $permissions
            ];
        } else {
            throw new InvalidArgumentException(sprintf(
                'Link must be an associative array, received %s',
                (is_object($link) ? get_class($link) : gettype($link))
            ));
        }

        return $this;
    }

    /**
     * Retrieve the secondary menu links.
     *
     * @return ArrayIterator|\Generator
     */
    public function links()
    {
        if (!is_array($this->links) && !($this->links instanceof \Traversable)) {
            $this->links = [];
        }

        foreach ($this->links as $link) {
            if (isset($link['active']) && !$link['active']) {
                continue;
            }

            if (isset($link['required_acl_permissions'])) {
                $link['permissions'] = $link['required_acl_permissions'];
                unset($link['required_acl_permissions']);
            }

            if (isset($link['permissions'])) {
                if ($this->hasPermissions($link['permissions']) === false) {
                    continue;
                }
            }

            yield $link;
        }
    }

    /**
     * Determine if the secondary menu has any links.
     *
     * @return boolean
     */
    public function hasLinks()
    {
        return !!$this->numLinks();
    }

    /**
     * Count the number of secondary menu links.
     *
     * @return integer
     */
    public function numLinks()
    {
        if (!is_array($this->links) && !($this->links instanceof \Traversable)) {
            return 0;
        }

        $links = array_filter($this->links, function ($link) {
            if (isset($link['active']) && !$link['active']) {
                return false;
            }

            if (isset($link['required_acl_permissions'])) {
                $link['permissions'] = $link['required_acl_permissions'];
                unset($link['required_acl_permissions']);
            }

            if (isset($link['permissions'])) {
                if ($this->hasPermissions($link['permissions']) === false) {
                    return false;
                }
            }

            return true;
        });

        return count($links);
    }

    /**
     * Set whether the item is selected or not.
     *
     * @param  boolean|null $flag Whether the item is selected or not.
     * @return boolean
     */
    public function isSelected($flag = null)
    {
        if ($flag !== null) {
            $this->isSelected = !!$flag;

            $this->setCollapsed(!$flag);
        }

        return $this->isSelected;
    }

    /**
     * Determine if the secondary groups should be displayed as panels.
     *
     * @return boolean
     */
    public function displayAsPanel()
    {
        return in_array($this->displayType(), [ 'panel', 'collapsible' ]);
    }

    /**
     * Determine if the group is collapsible.
     *
     * @return boolean
     */
    public function collapsible()
    {
        return ($this->displayType() === 'collapsible');
    }

    /**
     * Set whether the group is collapsed or not.
     *
     * @param  boolean $flag Whether the group is collapsed or not.
     * @return self
     */
    public function setCollapsed($flag)
    {
        $this->collapsed = !!$flag;

        return $this;
    }

    /**
     * Determine if the group is collapsed.
     *
     * @return boolean
     */
    public function collapsed()
    {
        $collapsed = $this->collapsible();

        if (is_bool($this->collapsed)) {
            $collapsed = $this->collapsed;
        }

        if (is_bool($this->isSelected())) {
            $collapsed = !$this->isSelected;
        }

        return $collapsed;
    }

    /**
     * Set whether the group is related to other groups.
     *
     * @param  boolean $flag Whether the group has siblings or not.
     * @return self
     */
    public function setParented($flag)
    {
        $this->parented = !!$flag;

        return $this;
    }

    /**
     * Determine if the group is related to other groups.
     *
     * @return boolean
     */
    public function parented()
    {
        return $this->parented;
    }

    /**
     * Set the identifier of the group.
     *
     * @param  string $ident The group identifier.
     * @return self
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the idenfitier of the group.
     *
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set whether the group is active or not.
     *
     * @param  boolean $active The active flag.
     * @return self
     */
    public function setActive($active)
    {
        $this->active = !!$active;

        return $this;
    }

    /**
     * Determine if the group is active or not.
     *
     * @return boolean
     */
    public function active()
    {
        return $this->active;
    }
}
