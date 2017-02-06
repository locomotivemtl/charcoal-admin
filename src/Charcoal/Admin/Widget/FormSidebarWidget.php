<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationConfig;

// From 'charcoal-ui'
use \Charcoal\Ui\Form\FormInterface;

// From 'charcoal-admin'
use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\Ui\ActionContainerTrait;
use \Charcoal\Admin\Ui\FormSidebarInterface;
use \Charcoal\Admin\User\AuthAwareInterface;
use \Charcoal\Admin\User\AuthAwareTrait;

/**
 * Form Sidebar Widget
 */
class FormSidebarWidget extends AdminWidget implements
    FormSidebarInterface,
    AuthAwareInterface
{
    use ActionContainerTrait;
    use AuthAwareTrait;

    /**
     * Default sorting priority for an action.
     *
     * @const integer
     */
    const DEFAULT_ACTION_PRIORITY = 10;

    /**
     * Store a reference to the parent form widget.
     *
     * @var FormInterface
     */
    private $form;

    /**
     * @var string
     */
    private $widgetType = 'properties';

    /**
     * Store the sidebar actions.
     *
     * @var array|null
     */
    private $sidebarActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    private $defaultSidebarActions;

    /**
     * Keep track if sidebar actions are finalized.
     *
     * @var boolean
     */
    protected $parsedSidebarActions = false;

    /**
     * @var array $sidebarProperties
     */
    protected $sidebarProperties = [];

    /**
     * Priority, or sorting index.
     * @var integer $priority
     */
    protected $priority;

    /**
     * @var Translation $title
     */
    protected $title;

    /**
     * @var boolean
     */
    protected $showFooter = true;

    /**
     * The required Acl permissions fetch from sidebar for specif parts.
     *
     * @var string[] $requiredAclPermissions
     */
    private $requiredAclPermissions = [];

    /**
     * The required Acl permissions for the whole sidebar.
     *
     * @var string[] $requiredGlobalAclPermissions
     */
    private $requiredGlobalAclPermissions = [];

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->setAuthDependencies($container);
    }

    /**
     * @param array|ArrayInterface $data Class data.
     * @return FormSidebarWidget Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['properties']) && $data['properties'] !== null) {
            $this->setSidebarProperties($data['properties']);
        }

        if (isset($data['actions']) && $data['actions'] !== null) {
            $this->setSidebarActions($data['actions']);
        }

        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
            $isAssoc = $this->isAssoc($permissions);
            if ($isAssoc) {
                $this->setRequiredAclPermissions($permissions);
            } else {
                $this->setRequiredGlobalAclPermissions($permissions);
            }
        }

        return $this;
    }

    /**
     * Set the form widget the sidebar belongs to.
     *
     * @param FormInterface $form The related form widget.
     * @return FormSidebarWidget Chainable
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Retrieve the form widget the sidebar belongs to.
     *
     * @return FormInterface
     */
    public function form()
    {
        return $this->form;
    }

    /**
     * @param mixed $subtitle The sidebar subtitle.
     * @return FormSidebarWidget Chainable
     */
    public function setSubtitle($subtitle)
    {
        $this->translator()->translation($subtitle);

        return $this;
    }

    /**
     * @param mixed $properties The sidebar properties.
     * @return FormSidebarWidget Chainable
     */
    public function setSidebarProperties($properties)
    {
        $this->sidebarProperties = $properties;

        return $this;
    }

    /**
     * @return mixed
     */
    public function sidebarProperties()
    {
        return $this->sidebarProperties;
    }

    /**
     * Determine if the form has any groups.
     *
     * @return boolean
     */
    public function hasSidebarProperties()
    {
        return ($this->numSidebarProperties() > 0);
    }

    /**
     * Count the number of form groups.
     *
     * @return integer
     */
    public function numSidebarProperties()
    {
        return count($this->sidebarProperties());
    }

    /**
     * Retrieve the object's properties from the form.
     *
     * @return mixed|Generator
     */
    public function formProperties()
    {
        $sidebarProperties = $this->sidebarProperties();
        $obj               = $this->form()->obj();
        $properties        = array_intersect(array_keys($obj->metadata()->properties()), $sidebarProperties);
        $ret               = [];
        foreach ($properties as $propertyIdent) {
            $property = $obj->p($propertyIdent);
            $val      = $obj[$propertyIdent];

            yield $propertyIdent => [
                'prop'       => $property,
                'displayVal' => $property->displayVal($val)
            ];
        }
    }

    /**
     * Determine if the sidebar's actions should be shown.
     *
     * @return boolean
     */
    public function showSidebarActions()
    {
        $actions = $this->sidebarActions();

        return count($actions);
    }

    /**
     * Retrieve the sidebar's actions.
     *
     * @return array
     */
    public function sidebarActions()
    {
        if ($this->sidebarActions === null) {
            $this->setSidebarActions([]);
        }

        if ($this->parsedSidebarActions === false) {
            $this->parsedSidebarActions = true;
            $this->sidebarActions = $this->createSidebarActions($this->sidebarActions);
        }

        return $this->sidebarActions;
    }

    /**
     * Set the sidebar's actions.
     *
     * @param  array $actions One or more actions.
     * @return FormSidebarWidget Chainable.
     */
    protected function setSidebarActions(array $actions)
    {
        $this->parsedSidebarActions = false;

        $this->sidebarActions = $this->mergeActions($this->defaultSidebarActions(), $actions);

        return $this;
    }

    /**
     * Build the sidebar's actions.
     *
     * Sidebar actions should come from the form settings defined by the "sidebars".
     * It is still possible to completly override those externally by setting the "actions"
     * with the {@see self::setSidebarActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array Sidebar actions.
     */
    protected function createSidebarActions(array $actions)
    {
        $this->actionsPriority = $this->defaultActionPriority();

        $sidebarActions = $this->parseAsSidebarActions($actions);

        return $sidebarActions;
    }

    /**
     * Parse the given actions as object actions.
     *
     * @param  array $actions Actions to resolve.
     * @return array
     */
    protected function parseAsSidebarActions(array $actions)
    {
        $sidebarActions = [];
        foreach ($actions as $ident => $action) {
            $ident  = $this->parseActionIdent($ident, $action);
            $action = $this->parseActionItem($action, $ident, true);

            if (!isset($action['priority'])) {
                $action['priority'] = $this->actionsPriority++;
            }

            if ($action['ident'] === 'view' && !$this->isObjViewable()) {
                $action['active'] = false;
            } elseif ($action['ident'] === 'save' && !$this->isObjSavable()) {
                $action['active'] = false;
            } elseif ($action['ident'] === 'reset' && !$this->isObjResettable()) {
                $action['active'] = false;
            } elseif ($action['ident'] === 'delete' && !$this->isObjDeletable()) {
                $action['active'] = false;
            }

            if ($action['isSubmittable'] && !$this->isObjSavable()) {
                $action['active'] = false;
            }

            if ($action['actions']) {
                $action['actions']    = $this->parseAsSidebarActions($action['actions']);
                $action['hasActions'] = !!array_filter($action['actions'], function ($action) {
                    return $action['active'];
                });
            }

            if (isset($sidebarActions[$ident])) {
                $hasPriority = ($action['priority'] > $sidebarActions[$ident]['priority']);
                if ($hasPriority || $action['isSubmittable']) {
                    $sidebarActions[$ident] = array_replace($sidebarActions[$ident], $action);
                } else {
                    $sidebarActions[$ident] = array_replace($action, $sidebarActions[$ident]);
                }
            } else {
                $sidebarActions[$ident] = $action;
            }
        }

        usort($sidebarActions, [ $this, 'sortActionsByPriority' ]);

        while (($first = reset($sidebarActions)) && $first['isSeparator']) {
            array_shift($sidebarActions);
        }

        while (($last = end($sidebarActions)) && $last['isSeparator']) {
            array_pop($sidebarActions);
        }

        return $sidebarActions;
    }

    /**
     * Retrieve the table's default object actions.
     *
     * @return array
     */
    protected function defaultSidebarActions()
    {
        if ($this->defaultSidebarActions === null) {
            $save = [
                'label'      => $this->form()->submitLabel(),
                'ident'      => 'save',
                'buttonType' => 'submit',
                'priority'   => 90
            ];
            $this->defaultSidebarActions = [ $save ];
        }

        return $this->defaultSidebarActions;
    }

    /**
     * @return string
     */
    public function jsActionPrefix()
    {
        return 'js-sidebar';
    }

    /**
     * Determine if the object can be deleted.
     *
     * If TRUE, the "Delete" button is shown. The object can still be
     * deleted programmatically or via direct action on the database.
     *
     * @return boolean
     */
    public function isObjDeletable()
    {
        // Overridden by permissions
        if (!$this->checkPermission('delete')) {
            return false;
        }

        $obj    = $this->form()->obj();
        $method = [ $obj, 'isDeletable' ];

        if (is_callable($method)) {
            return call_user_func($method);
        }

        return !!$obj->id();
    }

    /**
     * Determine if the object can be reset.
     *
     * If TRUE, the "Reset" button is shown. The object can still be
     * reset to its default values programmatically or emptied via direct
     * action on the database.
     *
     * @return boolean
     */
    public function isObjResettable()
    {
        // Overridden by permissions
        if (!$this->checkPermission('reset')) {
            return false;
        }

        $obj    = $this->form()->obj();
        $method = [ $obj, 'isResettable' ];

        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be saved.
     *
     * If TRUE, the "Save" button is shown. The object can still be
     * saved programmatically.
     *
     * @return boolean
     */
    public function isObjSavable()
    {
        // Overridden by permissions
        if (!$this->checkPermission('save')) {
            return false;
        }

        $obj    = $this->form()->obj();
        $method = [ $obj, 'isSavable' ];

        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be viewed (on the front-end).
     *
     * If TRUE, any "View" button is shown. The object can still be
     * saved programmatically.
     *
     * @return boolean
     */
    public function isObjViewable()
    {
        // Overridden by permissions
        if (!$this->checkPermission('view')) {
            return false;
        }

        $obj = $this->form()->obj();
        if (!$obj->id()) {
            return false;
        }

        $method = [ $obj, 'isViewable' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Set the widget's priority or sorting index.
     *
     * @param integer $priority An index, for sorting.
     * @throws InvalidArgumentException If the priority is not a number.
     * @return FormSidebarWidget Chainable
     */
    public function setPriority($priority)
    {
        if (!is_numeric($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }

        $this->priority = (int)$priority;

        return $this;
    }

    /**
     * Retrieve the widget's priority or sorting index.
     *
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $title The sidebar title.
     * @return FormSidebarWidget Chainable
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * @return Translation
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle('Actions');
        }

        return $this->title;
    }

    /**
     * @return boolean
     */
    public function showFooter()
    {
        // Overridden by permissions
        if (!$this->checkPermission('footer')) {
            return false;
        }

        return $this->showFooter;
    }

    /**
     * @param mixed $showFooter The show footer flag.
     * @return FormSidebarWidget
     */
    public function setShowFooter($showFooter)
    {
        $this->showFooter = !!$showFooter;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showLanguageSwitch()
    {
        $trans = TranslationConfig::instance();

        if ($trans->isMultilingual()) {
            foreach ($this->form()->formProperties() as $prop) {
                if ($prop->prop()->l10n()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieve the available languages, formatted for the sidebar language-switcher.
     *
     * @return array|Generator
     */
    public function languages()
    {
        $trans   = TranslationConfig::instance();
        $curLang = $trans->currentLanguage();
        $langs   = $trans->languages();

        foreach ($langs as $lang) {
            yield [
                'ident'   => $lang->ident(),
                'name'    => $lang->name(),
                'current' => ($lang->ident() === $curLang)
            ];
        }
    }



    // ACL Permissions
    // =========================================================================

    /**
     * Return true if the user as required permissions.
     *
     * @param string $permissionName The permission name to check against the user's permissions.
     * @return boolean
     */
    protected function checkPermission($permissionName)
    {
        if (!isset($this->requiredAclPermissions[$permissionName])) {
            return true;
        }

        $permissions = $this->requiredAclPermissions[$permissionName];

        // Test sidebar vs. ACL roles
        $authUser = $this->authenticator()->authenticate();
        if (!$this->authorizer()->userAllowed($authUser, $permissions)) {
            header('HTTP/1.0 403 Forbidden');
            header('Location: '.$this->adminUrl().'login');

            return false;
        }

        return true;
    }

    /**
     * @return string[]
     */
    public function requiredAclPermissions()
    {
        return $this->requiredAclPermissions;
    }

    /**
     * @param array $permissions The Acl permissions required pby the form group.
     * @return self
     */
    public function setRequiredAclPermissions(array $permissions)
    {
        $this->requiredAclPermissions = $permissions;

        return $this;
    }

    /**
     * @return string[]
     */
    public function requiredGlobalAclPermissions()
    {
        return $this->requiredGlobalAclPermissions;
    }

    /**
     * @param array $permissions The GlobalAcl permissions required pby the form group.
     * @return self
     */
    public function setRequiredGlobalAclPermissions(array $permissions)
    {
        $this->requiredGlobalAclPermissions = $permissions;

        return $this;
    }



    // Utilities
    // =========================================================================

    /**
     * @param array $array Detect if $array is assoc or not.
     * @return boolean
     */
    protected function isAssoc(array $array)
    {
        if ($array === []) {
            return false;
        }

        return !!array_filter($array, 'is_string', ARRAY_FILTER_USE_KEY);
    }
}
