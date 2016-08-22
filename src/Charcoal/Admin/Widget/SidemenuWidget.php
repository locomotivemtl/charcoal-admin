<?php

namespace Charcoal\Admin\Widget;

use ArrayIterator;
use RuntimeException;
use InvalidArgumentException;

use Pimple\Container;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-translation'
use Charcoal\Translation\TranslationString;

// Local Dependency
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\Sidemenu\SidemenuGroupInterface;

/**
 * Admin Sidemenu Widget
 */
class SidemenuWidget extends AdminWidget implements
    SidemenuWidgetInterface
{
    /**
     * The sidemenu's display type.
     *
     * @var string
     */
    protected $displayType;

    /**
     * The sidemenu's display options.
     *
     * @var array
     */
    protected $displayOptions;

    /**
     * Whether the group is collapsed or not.
     *
     * @var boolean
     */
    private $collapsed = false;

    /**
     * Whether the group has siblings or not.
     *
     * @var boolean
     */
    private $parented = false;

    /**
     * The sidemenu's title.
     *
     * @var string
     */
    protected $title;

    /**
     * The sidemenu's links.
     *
     * @var array
     */
    protected $links;

    /**
     * The sidemenu's groups.
     *
     * @var SidemenuGroupInterface[]
     */
    protected $groups;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $sidemenuGroupFactory;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setSidemenuGroupFactory($container['sidemenu/group/factory']);
    }

    /**
     * Set an sidemenu group factory.
     *
     * @param FactoryInterface $factory The group factory, to create objects.
     * @return self
     */
    public function setSidemenuGroupFactory(FactoryInterface $factory)
    {
        $this->sidemenuGroupFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the sidemenu group factory.
     *
     * @throws RuntimeException If the sidemenu group factory was not previously set.
     * @return FactoryInterface
     */
    protected function sidemenuGroupFactory()
    {
        if (!isset($this->sidemenuGroupFactory)) {
            throw new RuntimeException(
                sprintf('Sidemenu Group Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->sidemenuGroupFactory;
    }

    /**
     * Retrieve the metadata for the sidemenu.
     *
     * @return array
     */
    public function adminSidemenu()
    {
        return $this->adminConfig['sidemenu'];
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @return string|null
     */
    public function objType()
    {
        return filter_input(INPUT_GET, 'obj_type', FILTER_SANITIZE_STRING);
    }

    /**
     * Set the title of the sidemenu.
     *
     * @param  mixed $title A title for the sidemenu.
     * @return self
     */
    public function setTitle($title)
    {
        if (TranslationString::isTranslatable($title)) {
            $this->title = new TranslationString($title);
        } else {
            $this->title = null;
        }

        return $this;
    }

    /**
     * Retrieve the title of the sidemenu.
     *
     * @return TranslationString|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

            $this->title = '';

            if (isset($metadata[$ident]['title'])) {
                if (TranslationString::isTranslatable($metadata[$ident]['title'])) {
                    $this->setTitle($metadata[$ident]['title']);
                }
            }
        }

        return $this->title;
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
     * @param string       $linkIdent The link identifier.
     * @param array|object $link      The link object or structure.
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

        $objType = $this->objType();

        if (is_array($link)) {
            $name = null;
            $url  = null;

            if (isset($link['name']) && TranslationString::isTranslatable($link['name'])) {
                $name = new TranslationString($link['name']);
            }

            if (isset($link['url']) && TranslationString::isTranslatable($link['url'])) {
                $url = new TranslationString($link['url']);
            }

            if ($name === null && $url === null) {
                return $this;
            }

            $this->links[$linkIdent] = [
                'name'     => $name,
                'url'      => $url,
                'selected' => ($linkIdent === $objType)
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
        if ($this->links === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

            if (isset($metadata[$ident]['links'])) {
                $links = $metadata[$ident]['links'];

                if (is_array($links)) {
                    $this->setLinks($links);
                }
            }
        }

        return $this->links;
    }

    /**
     * Set the display type of the sidemenu's contents.
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
     * Retrieve the display type of the sidemenu's contents.
     *
     * @return string|null
     */
    public function displayType()
    {
        if ($this->displayType === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

            if (isset($metadata[$ident]['display_type'])) {
                $this->setDisplayType($metadata[$ident]['display_type']);
            } else {
                $this->displayType = '';
            }
        }

        return $this->displayType;
    }

    /**
     * Determine if the display type is "collapsible".
     *
     * @return boolean
     */
    public function collapsible()
    {
        return ($this->displayType() === 'collapsible');
    }

    /**
     * Set the display options for the sidemenu.
     *
     * @param  array $options Display configuration.
     * @throws InvalidArgumentException If the display options are not an associative array.
     * @return self
     */
    public function setDisplayOptions(array $options)
    {
        $this->displayOptions = $options;

        return $this;
    }

    /**
     * Retrieve the display options for the sidemenu.
     *
     * @throws RuntimeException If the display options are not an associative array.
     * @return array
     */
    public function displayOptions()
    {
        if ($this->displayOptions === null) {
            $this->setDisplayOptions($this->defaultDisplayOptions());

            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

            if (isset($metadata[$ident]['display_options'])) {
                $options = $metadata[$ident]['display_options'];

                if (!is_array($options)) {
                    throw new RuntimeException('The display options must be an associative array.');
                }

                $this->setDisplayOptions(array_merge($this->displayOptions, $options));
            }
        }

        return $this->displayOptions;
    }

    /**
     * Retrieve the default display options for the sidemenu.
     *
     * @return array
     */
    public function defaultDisplayOptions()
    {
        return [
            'parented'  => false,
            'collapsed' => $this->collapsible()
        ];
    }

    /**
     * @return mixed
     */
    public function parented()
    {
        if ($this->parented) {
            return $this->parented;
        }

        return $this->displayOptions()['parented'];
    }

    /**
     * @return mixed
     */
    public function collapsed()
    {
        if ($this->collapsed) {
            return $this->collapsed;
        }

        return $this->displayOptions()['collapsed'];
    }

    /**
     * Set the sidemenu's groups.
     *
     * @param  array $groups A collection of group structures.
     * @return self
     */
    public function setGroups(array $groups)
    {
        $this->groups = [];

        foreach ($groups as $groupIdent => $group) {
            $this->addGroup($groupIdent, $group);
        }

        uasort($this->groups, [ $this, 'sortGroupsByPriority' ]);

        return $this;
    }

    /**
     * Add a sidemenu group.
     *
     * @param string                       $groupIdent The group identifier.
     * @param array|SidemenuGroupInterface $group      The group object or structure.
     * @throws InvalidArgumentException If the identifier is not a string or the group is invalid.
     * @return FormInterface Chainable
     */
    public function addGroup($groupIdent, $group)
    {
        if (!is_string($groupIdent)) {
            throw new InvalidArgumentException(
                'Group identifier must be a string'
            );
        }

        if ($group instanceof SidemenuGroupInterface) {
            $group->setSidemenu($this);
            $group->setIdent($groupIdent);

            $this->groups[] = $group;
        } elseif (is_array($group)) {
            if (isset($group['ident'])) {
                $groupIdent = $group['ident'];
            } else {
                $group['ident'] = $groupIdent;
            }

            $displayOptions = $this->displayOptions();
            if (isset($group['display_options'])) {
                $displayOptions = array_merge($displayOptions, $group['display_options']);
            }

            $group['collapsed'] = $displayOptions['collapsed'];
            $group['parented']  = $displayOptions['parented'];

            if (!isset($group['display_type'])) {
                $group['display_type'] = $this->displayType();
            }

            error_log(var_export($group, true));

            $collapsible = ($group['display_type'] === 'collapsible');

            if ($collapsible) {
                $group['group_id'] = uniqid('collapsible_');
            }

            $g = $this->sidemenuGroupFactory()->create($this->defaultGroupType());
            $g->setSidemenu($this);
            $g->setData($group);

            $this->groups[] = $g;
        } elseif ($group === false || $group === null) {
            continue;
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Group must be an instance of %s or an array of form group options, received %s',
                    'SidemenuGroupInterface',
                    (is_object($group) ? get_class($group) : gettype($group))
                )
            );
        }

        return $this;
    }

    /**
     * Retrieve the sidemenu groups.
     *
     * @return SidemenuGroupInterface[]
     */
    public function groups()
    {
        if ($this->groups === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

            if (isset($metadata[$ident]['groups'])) {
                $groups = $metadata[$ident]['groups'];

                if (is_array($groups)) {
                    $this->setGroups($groups);
                }
            }
        }

        return $this->groups;
    }

    /**
     * Retrieve the default sidemenu group class name.
     *
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/ui/sidemenu/generic';
    }

    /**
     * To be called with uasort()
     *
     * @param  SidemenuGroupInterface $a First group object to sort.
     * @param  SidemenuGroupInterface $b Second group object to sort.
     * @return integer
     */
    protected static function sortGroupsByPriority(
        SidemenuGroupInterface $a,
        SidemenuGroupInterface $b
    ) {
        $a = $a->priority();
        $b = $b->priority();

        return ($a < $b) ? (-1) : 1;
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
        return count($this->links());
    }

    /**
     * Determine if the sidemenu has any groups of links.
     *
     * @return boolean
     */
    public function hasGroups()
    {
        return !!$this->numGroups();
    }

    /**
     * Count the number of sidemenu groups.
     *
     * @return integer
     */
    public function numGroups()
    {
        return count($this->groups());
    }

    /**
     * Determine if the sidemenu has any actions.
     *
     * @return boolean
     */
    public function hasActions()
    {
        return false;
    }

    /**
     * Retrieve the sidemenu's actions.
     *
     * @return array
     */
    public function actions()
    {
        return [];
    }
}
