<?php

namespace Charcoal\Admin\Ui\Sidemenu;

use \ArrayIterator;
use \InvalidArgumentException;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationString;

// Local Dependency
use Charcoal\Admin\Widget\SidemenuWidgetInterface;

/**
 *
 */
trait SidemenuGroupTrait
{
    /**
     * Store a reference to the parent sidemenu widget.
     *
     * @var SidemenuWidgetInterface
     */
    protected $sidemenu;

    /**
     * The sidemenu group ID.
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
     * The group's priority.
     *
     * @var integer
     */
    private $priority;

    /**
     * Set the sidemenu widget.
     *
     * @param  SidemenuWidgetInterface $sidemenu The related sidemenu widget.
     * @return self
     */
    public function setSidemenu(SidemenuWidgetInterface $sidemenu)
    {
        $this->sidemenu = $sidemenu;

        return $this;
    }

    /**
     * Retrieve the sidemenu widget.
     *
     * @return SidemenuWidgetInterface
     */
    public function sidemenu()
    {
        return $this->sidemenu;
    }

    /**
     * Set the group ID.
     *
     * @param string $id The group ID.
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
            $this->groupId = uniqid('sidemenu_group_');
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
     * Set the sidemenu links.
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
     * Set the sidemenu links.
     *
     * @param string $linkIdent The link identifier.
     * @param array|object $link The link object or structure.
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

        $objType = $this->sidemenu()->objType();

        if (is_array($link)) {
            $name = null;
            $url = null;

            if (isset($link['name']) && TranslationString::isTranslatable($link['name'])) {
                $name = new TranslationString($link['name']);
            }

            if (isset($link['url']) && TranslationString::isTranslatable($link['url'])) {
                $url = new TranslationString($link['url']);
            }

            if ($name === null && $url === null) {
                return $this;
            }

            $isSelected = ($linkIdent === $objType);

            if ($isSelected) {
                $this->isSelected(true);
            }

            $this->links[$linkIdent] = [
                'name'     => $name,
                'url'      => $url,
                'selected' => $isSelected
            ];
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Link must be an associative array, received %2$s',
                    'MenuItemInterface',
                    (is_object($link) ? get_class($link) : gettype($link))
                )
            );
        }

        return $this;
    }

    /**
     * Retrieve the sidemenu links.
     *
     * @return ArrayIterator
     */
    public function links()
    {
        return $this->links;
    }

    /**
     * Determine if the sidemenu has any links.
     *
     * @return boolean
     */
    public function hasLinks()
    {
        return !!$this->numLinks();
    }

    /**
     * Count the number of sidemenu links.
     *
     * @return integer
     */
    public function numLinks()
    {
        return count($this->links);
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

        if(is_bool($this->collapsed)) {
            $collapsed = $this->collapsed;
        }

        if(is_bool($this->isSelected())) {
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
     * @param string $ident The group identifier.
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

    /**
     * Set the group's priority or sorting index.
     *
     * @param  integer $priority An index, for sorting.
     * @throws InvalidArgumentException If the priority is not an integer.
     * @return self
     */
    public function setPriority($priority)
    {
        if (!is_numeric($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }

        $this->priority = intval($priority);

        return $this;
    }

    /**
     * Retrieve the group's priority or sorting index.
     *
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }
}
