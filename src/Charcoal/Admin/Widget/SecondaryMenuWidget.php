<?php

namespace Charcoal\Admin\Widget;

use ArrayIterator;
use RuntimeException;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Support\HttpAwareTrait;
use Charcoal\Admin\Ui\ActionContainerTrait;
use Charcoal\Admin\Ui\SecondaryMenu\SecondaryMenuGroupInterface;

/**
 * Admin Secondary Menu Widget
 */
class SecondaryMenuWidget extends AdminWidget implements
    SecondaryMenuWidgetInterface
{
    use ActionContainerTrait;
    use HttpAwareTrait;

    /**
     * Default sorting priority for an action.
     *
     * @const integer
     */
    const DEFAULT_ACTION_PRIORITY = 10;

    /**
     * Store the secondary menu actions.
     *
     * @var array|null
     */
    protected $secondaryMenuActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    protected $defaultSecondaryMenuActions;

    /**
     * Keep track if secondary menu actions are finalized.
     *
     * @var boolean
     */
    protected $parsedSecondaryMenuActions = false;

    /**
     * The secondary menu's display type.
     *
     * @var string
     */
    protected $displayType;

    /**
     * The secondary menu's display options.
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
     * The title is displayed by default.
     *
     * @var boolean
     */
    private $showTitle = true;

    /**
     * The description is displayed by default.
     *
     * @var boolean
     */
    private $showDescription = true;

    /**
     * The currently highlighted item.
     *
     * @var mixed
     */
    protected $currentItem;

    /**
     * The admin's current route.
     *
     * @var UriInterface
     */
    protected $adminRoute;

    /**
     * The secondary menu's title.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    protected $title;

    /**
     * The secondary menu's links.
     *
     * @var array
     */
    protected $links;

    /**
     * The secondary menu's groups.
     *
     * @var SecondaryMenuGroupInterface[]
     */
    protected $groups;

    /**
     * The secondary menu's description.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    protected $description;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $secondaryMenu;

    /**
     * @param  array $data Class data.
     * @return self
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['actions'])) {
            $this->setSecondaryMenuActions($data['actions']);
        }

        if (isset($data['is_current'])) {
            $this->setIsCurrent($data['is_current']);
        }

        return $this;
    }

    /**
     * Determine if the secondary menu has anything.
     *
     * @return boolean
     */
    public function hasSecondaryMenu()
    {
        $ident    = $this->ident();
        $metadata = $this->adminSecondaryMenu();

        if (isset($metadata[$ident])) {
            return $this->hasLinks() ||
                   $this->hasGroups() ||
                   $this->hasActions() ||
                   $this->showTitle() ||
                   $this->showDescription();
        }

        return false;
    }

    /**
     * Determine if the secondary menu is accessible via a tab.
     *
     * @return boolean
     */
    public function isTabbed()
    {
        $ident    = $this->ident();
        $metadata = $this->adminSecondaryMenu();

        if (isset($metadata[$ident])) {
            return $this->hasLinks() ||
                   $this->hasGroups() ||
                   $this->hasActions();
        }

        return false;
    }

    /**
     * Retrieve the metadata for the secondary menu.
     *
     * @return array
     */
    public function adminSecondaryMenu()
    {
        return $this->adminConfig('secondary_menu', []);
    }

    /**
     * Retrieve the current route path.
     *
     * @return string|null
     */
    public function adminRoute()
    {
        if ($this->adminRoute === null) {
            $requestUri = (string)$this->httpRequest()->getUri();
            $requestUri = str_replace($this->adminUrl(), '', $requestUri);

            $this->adminRoute = $requestUri;
        }

        return $this->adminRoute;
    }

    /**
     * @param  string $ident The ident for the current item to highlight.
     * @return self
     */
    public function setCurrentItem($ident)
    {
        $this->currentItem = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function currentItem()
    {
        if ($this->currentItem === null) {
            return $this->objType() ?: $this->adminRoute()->getPath();
        }

        return $this->currentItem;
    }

    /**
     * Computes the intersection of values to determine if the link is the current item.
     *
     * @param  mixed $linkIdent The link's value(s) to check.
     * @return boolean
     */
    public function isCurrentItem($linkIdent)
    {
        $context = array_filter([
            $this->currentItem,
            $this->objType(),
            $this->adminRoute(),
        ]);

        $matches = array_intersect((array)$linkIdent, $context);

        return !!$matches;
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @return string|null
     */
    public function objType()
    {
        return $this->httpRequest()->getParam('obj_type');
    }

    /**
     * Show/hide the widget's title.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the title.
     * @return self
     */
    public function setShowTitle($show)
    {
        $this->showTitle = !!$show;

        return $this;
    }

    /**
     * Determine if the title is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a title.
     */
    public function showTitle()
    {
        if ($this->showTitle === false) {
            return false;
        } else {
            return !!$this->title();
        }
    }

    /**
     * Set the title of the secondary menu.
     *
     * @param  mixed $title A title for the secondary menu.
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Retrieve the title of the secondary menu.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->title = '';

            if (isset($metadata[$ident]['title'])) {
                $this->setTitle($metadata[$ident]['title']);
            }
        }

        return $this->title;
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
            $name   = null;
            $url    = null;
            $permissions = [];

            if (isset($link['ident'])) {
                $linkIdent = $link['ident'];
            } else {
                $link['ident'] = $linkIdent;
            }

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

            $this->links[$linkIdent] = [
                'active'   => $active,
                'name'     => $name,
                'url'      => $url,
                'selected' => $this->isCurrentItem([ $linkIdent, (string)$url ]),
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
     * @return array
     */
    public function links()
    {
        if ($this->links === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->links = [];
            if (isset($metadata[$ident]['links'])) {
                $links = $metadata[$ident]['links'];

                if (is_array($links)) {
                    $this->setLinks($links);
                }
            }
        }

        $out = [];

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

            $out[] = $link;
        }

        $this->links = $out;
        return $this->links;
    }

    /**
     * Set the display type of the secondary menu's contents.
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
     * Retrieve the display type of the secondary menu's contents.
     *
     * @return string|null
     */
    public function displayType()
    {
        if ($this->displayType === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            if (isset($metadata[$ident]['display_type'])) {
                $this->setDisplayType($metadata[$ident]['display_type']);
            } else {
                $this->displayType = '';
            }
        }

        return $this->displayType;
    }

    /**
     * Determine if the secondary menu groups should be displayed as panels.
     *
     * @return boolean
     */
    public function displayAsPanel()
    {
        return in_array($this->displayType(), [ 'panel', 'collapsible' ]);
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
     * Set the display options for the secondary menu.
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
     * Retrieve the display options for the secondary menu.
     *
     * @throws RuntimeException If the display options are not an associative array.
     * @return array
     */
    public function displayOptions()
    {
        if ($this->displayOptions === null) {
            $this->setDisplayOptions($this->defaultDisplayOptions());

            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

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
     * Retrieve the default display options for the secondary menu.
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
     * Set the secondary menu's groups.
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

        // Remove items that are not active.
        $this->groups = array_filter($this->groups, function($item) {
            return ($item->active());
        });

        return $this;
    }

    /**
     * Add a secondary menu group.
     *
     * @param  string                       $groupIdent The group identifier.
     * @param  array|SecondaryMenuGroupInterface $group      The group object or structure.
     * @throws InvalidArgumentException If the identifier is not a string or the group is invalid.
     * @return self
     */
    public function addGroup($groupIdent, $group)
    {
        if (!is_string($groupIdent)) {
            throw new InvalidArgumentException(
                'Group identifier must be a string'
            );
        }

        if ($group instanceof SecondaryMenuGroupInterface) {
            $group->setSecondaryMenu($this);
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

            $collapsible = ($group['display_type'] === 'collapsible');

            if ($collapsible) {
                $group['group_id'] = uniqid('collapsible_');
            }

            $g = $this->secondaryMenu()->create($this->defaultGroupType());
            $g->setSecondaryMenu($this);
            $g->setData($group);

            $group = $g;
        } elseif ($group === false || $group === null) {
            return $this;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Group must be an instance of %s or an array of form group options, received %s',
                'SecondaryMenuGroupInterface',
                (is_object($group) ? get_class($group) : gettype($group))
            ));
        }

        if ($g->isAuthorized() === false) {
            return $this;
        }

        $this->groups[] = $g;

        return $this;
    }

    /**
     * Retrieve the secondary menu groups.
     *
     * @return array
     */
    public function groups()
    {
        if ($this->groups === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->groups = [];
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
     * Retrieve the default secondary menu group class name.
     *
     * @return string
     */
    public function defaultGroupType()
    {
        return 'charcoal/ui/secondary-menu/generic';
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
        if (!is_array($this->links()) && !($this->links() instanceof \Traversable)) {
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
     * Determine if the secondary menu has any groups of links.
     *
     * @return boolean
     */
    public function hasGroups()
    {
        return !!$this->numGroups();
    }

    /**
     * Count the number of secondary menu groups.
     *
     * @return integer
     */
    public function numGroups()
    {
        return count($this->groups());
    }

    /**
     * Alias for {@see self::showSecondaryMenuActions()}
     *
     * @return boolean
     */
    public function hasActions()
    {
        return $this->showSecondaryMenuActions();
    }

    /**
     * Determine if the secondary menu's actions should be shown.
     *
     * @return boolean
     */
    public function showSecondaryMenuActions()
    {
        $actions = $this->secondaryMenuActions();

        return count($actions);
    }

    /**
     * Retrieve the secondary menu's actions.
     *
     * @return array
     */
    public function secondaryMenuActions()
    {
        if ($this->secondaryMenuActions === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();
            if (isset($metadata[$ident]['actions'])) {
                $actions = $metadata[$ident]['actions'];
            } else {
                $actions = [];
            }
            $this->setSecondaryMenuActions($actions);
        }

        if ($this->parsedSecondaryMenuActions === false) {
            $this->parsedSecondaryMenuActions = true;
            $this->secondaryMenuActions = $this->createSecondaryMenuActions($this->secondaryMenuActions);
        }

        return $this->secondaryMenuActions;
    }

    /**
     * Set the description of the secondary menu.
     *
     * @param  mixed $description A description for the secondary menu.
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $this->translator()->translation($description);

        return $this;
    }

    /**
     * Retrieve the description of the secondary menu.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function description()
    {
        if ($this->description === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->description = '';

            if (isset($metadata[$ident]['description'])) {
                $this->setDescription($metadata[$ident]['description']);
            }
        }

        return $this->description;
    }
    /**
     * Determine if the description is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a description.
     */
    public function setShowDescription($show)
    {
        $this->showDescription = !!$show;
        return $this;
    }

    /**
     * Show/hide the widget's description.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the description.
     * @return self
     */
    public function showDescription()
    {
        if ($this->showDescription === false) {
            return false;
        } else {
            return !!$this->description();
        }
    }

    /**
     * @return string
     */
    public function jsActionPrefix()
    {
        return 'js-secondary-menu';
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies HttpAwareTrait dependencies
        $this->setHttpRequest($container['request']);

        $this->setSecondaryMenuGroupFactory($container['secondary-menu/group/factory']);
    }

    /**
     * Retrieve the widget's display state.
     *
     * @return boolean
     */
    public function isCurrent()
    {
        return $this->isCurrent;
    }

    /**
     * Set the widget's display state.
     *
     * @param  boolean $flag A truthy state.
     * @return self
     */
    protected function setIsCurrent($flag)
    {
        $this->isCurrent = boolval($flag);

        return $this;
    }

    /**
     * Retrieve the secondary menu group factory.
     *
     * @throws RuntimeException If the secondary menu group factory was not previously set.
     * @return FactoryInterface
     */
    protected function secondaryMenu()
    {
        if (!isset($this->secondaryMenu)) {
            throw new RuntimeException(sprintf(
                'Secondary Menu Group Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->secondaryMenu;
    }

    /**
     * Set the secondary menu's actions.
     *
     * @param  array $actions One or more actions.
     * @return self
     */
    protected function setSecondaryMenuActions(array $actions)
    {
        $this->parsedSecondaryMenuActions = false;

        $this->secondaryMenuActions = $this->mergeActions($this->defaultSecondaryMenuActions(), $actions);

        return $this;
    }

    /**
     * Build the secondary menu's actions.
     *
     * Secondary menu actions should come from the form settings defined by the "secondary menus".
     * It is still possible to completly override those externally by setting the "actions"
     * with the {@see self::setSecondaryMenuActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array Secondary menu actions.
     */
    protected function createSecondaryMenuActions(array $actions)
    {
        $secondaryMenuActions = $this->parseActions($actions);

        return $secondaryMenuActions;
    }

    /**
     * Retrieve the secondary menu's default actions.
     *
     * @return array
     */
    protected function defaultSecondaryMenuActions()
    {
        if ($this->defaultSecondaryMenuActions === null) {
            // $library = [
            //     'active'     => false,
            //     'label'      => $this->translator()->translation('File Manager'),
            //     'ident'      => 'filemanager',
            //     'url'        => $this->adminUrl().'media',
            //     'cssClasses' => 'js-toggle-filemanager',
            //     'priority'   => 90
            // ];
            // $this->defaultSecondaryMenuActions = [ $library ];
            $this->defaultSecondaryMenuActions = [];
        }

        return $this->defaultSecondaryMenuActions;
    }

    /**
     * To be called with {@see uasort()}.
     *
     * @param  SecondaryMenuGroupInterface $a Sortable entity A.
     * @param  SecondaryMenuGroupInterface $b Sortable entity B.
     * @return integer Sorting value: -1, 0, or 1
     */
    protected function sortGroupsByPriority(
        SecondaryMenuGroupInterface $a,
        SecondaryMenuGroupInterface $b
    ) {
        $a = $a->priority();
        $b = $b->priority();

        if ($a === $b) {
            return 0;
        }
        return ($a < $b) ? (-1) : 1;
    }

    /**
     * Set a secondary menu group factory.
     *
     * @param FactoryInterface $factory The group factory, to create objects.
     * @return void
     */
    private function setSecondaryMenuGroupFactory(FactoryInterface $factory)
    {
        $this->secondaryMenu = $factory;
    }
}
