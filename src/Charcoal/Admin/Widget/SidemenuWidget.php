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
use Charcoal\Admin\Ui\ActionContainerTrait;
use Charcoal\Admin\Ui\Sidemenu\SidemenuGroupInterface;

/**
 * Admin Sidemenu Widget
 */
class SidemenuWidget extends AdminWidget implements
    SidemenuWidgetInterface
{
    use ActionContainerTrait;

    /**
     * Default sorting priority for an action.
     *
     * @const integer
     */
    const DEFAULT_ACTION_PRIORITY = 10;

    /**
     * Store the sidemenu actions.
     *
     * @var array|null
     */
    protected $sidemenuActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    protected $defaultSidemenuActions;

    /**
     * Keep track if sidemenu actions are finalized.
     *
     * @var boolean
     */
    protected $parsedSidemenuActions = false;

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
     * The title is displayed by default.
     *
     * @var boolean
     */
    private $showTitle = true;

    /**
     * The admin's current route.
     *
     * @var UriInterface
     */
    protected $adminRoute;

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
     * Store the HTTP request object.
     *
     * @var RequestInterface
     */
    private $httpRequest;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setHttpRequest($container['request']);
        $this->setSidemenuGroupFactory($container['sidemenu/group/factory']);
    }

    /**
     * Set an HTTP request object.
     *
     * @param RequestInterface $request A PSR-7 compatible Request instance.
     * @return self
     */
    protected function setHttpRequest(RequestInterface $request)
    {
        $this->httpRequest = $request;

        return $this;
    }

    /**
     * Retrieve the HTTP request object.
     *
     * @throws RuntimeException If an HTTP request was not previously set.
     * @return RequestInterface
     */
    public function httpRequest()
    {
        if (!isset($this->httpRequest)) {
            throw new RuntimeException(sprintf(
                'A PSR-7 Request instance is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->httpRequest;
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
            throw new RuntimeException(sprintf(
                'Sidemenu Group Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->sidemenuGroupFactory;
    }

    /**
     * @param  array $data Class data.
     * @return self
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['actions'])) {
            $this->setSidemenuActions($data['actions']);
        }

        return $this;
    }

    /**
     * Determine if the sidemenu has anything.
     *
     * @return boolean
     */
    public function hasSidemenu()
    {
        $ident    = $this->ident();
        $metadata = $this->adminSidemenu();

        if (isset($metadata[$ident])) {
            return $this->hasLinks() || $this->hasGroups() || $this->hasActions() || $this->showTitle();
        }

        return false;
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
     * Retrieve the current route path.
     *
     * @return UriInterface|null
     */
    public function adminRoute()
    {
        if ($this->adminRoute === null) {
            $uri = $this->httpRequest()->getUri();
            $uri = $uri->withBasePath($this->adminConfig['base_path']);

            $path = str_replace($uri->getBasePath(), '', $uri->getPath());
            $path = ltrim($path, '/');
            $uri  = $uri->withPath($path);

            $this->adminRoute = $uri;
        }

        return $this->adminRoute;
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
     * Set the title of the sidemenu.
     *
     * @param  mixed $title A title for the sidemenu.
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Retrieve the title of the sidemenu.
     *
     * @return Translation|null
     */
    public function title()
    {
        if ($this->title === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

            $this->title = '';

            if (isset($metadata[$ident]['title'])) {
                $this->setTitle($metadata[$ident]['title']);
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

        $uriPath = $this->adminRoute()->getPath();
        $objType = $this->objType();

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
                'selected' => ($linkIdent === $objType || $linkIdent === $uriPath),
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
     * Retrieve the sidemenu links.
     *
     * @return array
     */
    public function links()
    {
        if ($this->links === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

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
     * Determine if the sidemenu groups should be displayed as panels.
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
     * @param  string                       $groupIdent The group identifier.
     * @param  array|SidemenuGroupInterface $group      The group object or structure.
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

            $collapsible = ($group['display_type'] === 'collapsible');

            if ($collapsible) {
                $group['group_id'] = uniqid('collapsible_');
            }

            $g = $this->sidemenuGroupFactory()->create($this->defaultGroupType());
            $g->setSidemenu($this);
            $g->setData($group);

            $group = $g;
        } elseif ($group === false || $group === null) {
            return $this;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Group must be an instance of %s or an array of form group options, received %s',
                'SidemenuGroupInterface',
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
     * Retrieve the sidemenu groups.
     *
     * @return SidemenuGroupInterface[]|Generator
     */
    public function groups()
    {
        if ($this->groups === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();

            $this->groups = [];
            if (isset($metadata[$ident]['groups'])) {
                $groups = $metadata[$ident]['groups'];

                if (is_array($groups)) {
                    $this->setGroups($groups);
                }
            }
        }

        foreach ($this->groups as $group) {
            if (!$group->active()) {
                continue;
            }

            yield $group;
        }
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
     * To be called with {@see uasort()}.
     *
     * @param  SidemenuGroupInterface $a Sortable entity A.
     * @param  SidemenuGroupInterface $b Sortable entity B.
     * @return integer Sorting value: -1, 0, or 1
     */
    protected function sortGroupsByPriority(
        SidemenuGroupInterface $a,
        SidemenuGroupInterface $b
    ) {
        $a = $a->priority();
        $b = $b->priority();

        if ($a === $b) {
            return 0;
        }
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
     * Alias for {@see self::showSidemenuActions()}
     *
     * @return boolean
     */
    public function hasActions()
    {
        return $this->showSidemenuActions();
    }

    /**
     * Determine if the sidemenu's actions should be shown.
     *
     * @return boolean
     */
    public function showSidemenuActions()
    {
        $actions = $this->sidemenuActions();

        return count($actions);
    }

    /**
     * Retrieve the sidemenu's actions.
     *
     * @return array
     */
    public function sidemenuActions()
    {
        if ($this->sidemenuActions === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSidemenu();
            if (isset($metadata[$ident]['actions'])) {
                $actions = $metadata[$ident]['actions'];
            } else {
                $actions = [];
            }
            $this->setSidemenuActions($actions);
        }

        if ($this->parsedSidemenuActions === false) {
            $this->parsedSidemenuActions = true;
            $this->sidemenuActions = $this->createSidemenuActions($this->sidemenuActions);
        }

        return $this->sidemenuActions;
    }

    /**
     * Set the sidemenu's actions.
     *
     * @param  array $actions One or more actions.
     * @return FormSidemenuWidget Chainable.
     */
    protected function setSidemenuActions(array $actions)
    {
        $this->parsedSidemenuActions = false;

        $this->sidemenuActions = $this->mergeActions($this->defaultSidemenuActions(), $actions);

        return $this;
    }

    /**
     * Build the sidemenu's actions.
     *
     * Sidemenu actions should come from the form settings defined by the "sidemenus".
     * It is still possible to completly override those externally by setting the "actions"
     * with the {@see self::setSidemenuActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array Sidemenu actions.
     */
    protected function createSidemenuActions(array $actions)
    {
        $sidemenuActions = $this->parseActions($actions);

        return $sidemenuActions;
    }

    /**
     * Retrieve the sidemenu's default actions.
     *
     * @return array
     */
    protected function defaultSidemenuActions()
    {
        if ($this->defaultSidemenuActions === null) {
            $library = [
                'active'     => false,
                'label'      => $this->translator()->translation('File Manager'),
                'ident'      => 'filemanager',
                'url'        => $this->adminUrl().'media',
                'cssClasses' => 'js-toggle-filemanager',
                'priority'   => 90
            ];
            $this->defaultSidemenuActions = [ $library ];
        }

        return $this->defaultSidemenuActions;
    }

    /**
     * @return string
     */
    public function jsActionPrefix()
    {
        return 'js-sidemenu';
    }
}
