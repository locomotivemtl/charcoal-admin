<?php

namespace Charcoal\Admin\Widget;

use Charcoal\Object\RevisionableInterface;
use Charcoal\User\AuthAwareInterface;
use InvalidArgumentException;

// From Pimple
use Pimple\Container;

// From 'charcoal-ui'
use Charcoal\Ui\Form\FormInterface;
use Charcoal\Admin\Ui\HasLanguageSwitcherInterface;
use Charcoal\Admin\Ui\HasLanguageSwitcherTrait;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Ui\ActionContainerTrait;
use Charcoal\Admin\Ui\FormSidebarInterface;
use Charcoal\Admin\Widget\FormWidget;

/**
 * Form Sidebar Widget
 */
class FormSidebarWidget extends AdminWidget implements
    FormSidebarInterface,
    HasLanguageSwitcherInterface
{
    use ActionContainerTrait;
    use HasLanguageSwitcherTrait;

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
     * Store the sidebar actions.
     *
     * @var array|null
     */
    protected $sidebarActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    protected $defaultSidebarActions;

    /**
     * Keep track if sidebar actions are finalized.
     *
     * @var boolean
     */
    protected $parsedSidebarActions = false;

    /**
     * The properties to show.
     *
     * @var array
     */
    protected $sidebarProperties = [];

    /**
     * Customize the shown properties.
     *
     * @var array
     */
    private $propertiesOptions = [];

    /**
     * The title is displayed by default.
     *
     * @var boolean
     */
    private $showTitle = true;

    /**
     * The sidebar's title.
     *
     * @var Translation|string|null
     */
    protected $title;

    /**
     * The subtitle is displayed by default.
     *
     * @var boolean
     */
    private $showSubtitle = true;

    /**
     * The sidebar's subtitle.
     *
     * @var Translation|string|null
     */
    private $subtitle;

    /**
     * The footer is displayed by default.
     *
     * @var boolean
     */
    protected $showFooter = true;

    /**
     * Whether to display the sidebar actions.
     *
     * @var boolean
     */
    protected $showSidebarActions;

    /**
     * Whether the object is viewable.
     *
     * @var boolean
     */
    private $isObjViewable;

    /**
     * Whether the object is savable.
     *
     * @var boolean
     */
    private $isObjSavable;

    /**
     * Whether the object is resettable.
     *
     * @var boolean
     */
    private $isObjResettable;

    /**
     * Whether the object is deletable.
     *
     * @var boolean
     */
    private $isObjDeletable;

    /**
     * Whether the object is revisionable.
     *
     * @var boolean
     */
    private $isObjRevisionable;

    /**
     * The required Acl permissions for the whole sidebar.
     *
     * @var string[]
     */
    private $requiredGlobalAclPermissions = [];

    /**
     * @param array|ArrayInterface $data Class data.
     * @return FormSidebarWidget Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['properties'])) {
            $this->setSidebarProperties($data['properties']);
        }

        if (isset($data['actions'])) {
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
     * Set the form's object properties to show in the sidebar.
     *
     * @param  array $properties The form's object properties.
     * @return FormSidebarWidget Chainable
     */
    public function setSidebarProperties(array $properties)
    {
        $this->sidebarProperties = $properties;

        return $this;
    }

    /**
     * Retrieve the form's object properties to show in the sidebar.
     *
     * @return mixed
     */
    public function sidebarProperties()
    {
        return $this->sidebarProperties;
    }

    /**
     * Determine if the sidebar has any object properties.
     *
     * @return boolean
     */
    public function hasSidebarProperties()
    {
        return ($this->numSidebarProperties() > 0);
    }

    /**
     * Count the number of object properties in the sidebar.
     *
     * @return integer
     */
    public function numSidebarProperties()
    {
        return count($this->sidebarProperties());
    }

    /**
     * Set the map of object property customizations.
     *
     * @param  array $properties The options to customize the group properties.
     * @return FormSidebarWidget Chainable
     */
    public function setPropertiesOptions(array $properties)
    {
        $this->propertiesOptions = $properties;

        return $this;
    }

    /**
     * Retrieve the map of object property customizations.
     *
     * @return array
     */
    public function propertiesOptions()
    {
        return $this->propertiesOptions;
    }

    /**
     * Retrieve the object's properties from the form.
     *
     * @return mixed|Generator
     */
    public function formProperties()
    {
        $form = $this->form();
        $obj  = $form->obj();

        $availableProperties = $obj->properties();
        $sidebarProperties   = $this->sidebarProperties();
        $propertiesOptions   = $this->propertiesOptions();

        foreach ($sidebarProperties as $propertyIdent) {
            if (!$obj->hasProperty($propertyIdent)) {
                continue;
            }

            $property = $obj->property($propertyIdent);
            $value    = $obj->propertyValue($propertyIdent);

            $formProperty = $form->createFormProperty();
            $formProperty->setOutputType($formProperty::PROPERTY_DISPLAY);
            $formProperty->setShowNotes(false);
            $formProperty->setViewController($form->viewController());

            $formProperty->setProperty($property);
            $formProperty->setPropertyIdent($property->ident());
            $formProperty->setPropertyVal($obj[$propertyIdent]);

            if (!empty($propertiesOptions[$propertyIdent])) {
                $propertyOptions = $propertiesOptions[$propertyIdent];

                if (is_array($propertyOptions)) {
                    $formProperty->setData($propertyOptions);
                }
            }

            if (!empty($formProperty['required_acl_permissions']) && $this instanceof AuthAwareInterface) {
                $formProperty['active'] = $this->hasPermissions($formProperty['required_acl_permissions']);
                if (!$this->hasPermissions($formProperty['required_acl_permissions'])) {
                    continue;
                }
            }

            yield $propertyIdent => $formProperty;
        }
    }

    /**
     * Determine if the sidebar's actions should be shown.
     *
     * @return boolean
     */
    public function showSidebarActions()
    {
        if ($this->showSidebarActions === null) {
            $actions = $this->sidebarActions();
            if (empty($actions)) {
                return false;
            }

            $actions = array_filter($actions, function($action) {
                return $action['active'] === true;
            });

            $this->showSidebarActions = (count($actions) > 0);
        }

        return $this->showSidebarActions;
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

        usort($sidebarActions, [ 'Charcoal\Admin\Support\Sorter', 'sortByPriority' ]);

        while (($first = reset($sidebarActions)) && $first['isSeparator']) {
            array_shift($sidebarActions);
        }

        while (($last = end($sidebarActions)) && $last['isSeparator']) {
            array_pop($sidebarActions);
        }

        return $sidebarActions;
    }

    /**
     * Retrieve the sidebar's default actions.
     *
     * @return array
     */
    protected function defaultSidebarActions()
    {
        if ($this->defaultSidebarActions === null) {
            $this->defaultSidebarActions = [];

            if ($this->form()) {
                $save = [
                    'label'      => $this->form()->submitLabel(),
                    'ident'      => 'save',
                    'buttonType' => 'submit',
                    'priority'   => 90
                ];
                $this->defaultSidebarActions[] = $save;
            }
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
        if ($this->isObjDeletable === null) {
            // Overridden by permissions
            if (!$this->checkPermission('delete') || !$this->form()) {
                $this->isObjDeletable = false;
            } else {
                $obj = $this->form()->obj();
                $this->isObjDeletable = !!$obj->id();

                $method = [ $obj, 'isDeletable' ];
                if (is_callable($method)) {
                    $this->isObjDeletable = call_user_func($method);
                }
            }
        }

        return $this->isObjDeletable;
    }

    /**
     * Determine if the object has revisions.
     *
     * If TRUE, the "Delete" button is shown. The object can still be
     * deleted programmatically or via direct action on the database.
     *
     * @return boolean
     */
    public function isObjRevisionable()
    {
        if ($this->isObjRevisionable === null) {
            // Overridden by permissions
            if (!$this->checkPermission('revision') || !$this->form()) {
                $this->isObjRevisionable = false;
            } else {
                $obj = $this->form()->obj();
                if (!$obj->id()) {
                    $this->isObjRevisionable = false;
                    return $this->isObjRevisionable;
                }

                if ($obj instanceof RevisionableInterface && $obj['revisionEnabled']) {
                    $this->isObjRevisionable = !!count($obj->allRevisions());
                }
            }
        }

        return $this->isObjRevisionable;
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
        if ($this->isObjResettable === null) {
            // Overridden by permissions
            if (!$this->checkPermission('reset') || !$this->form()) {
                $this->isObjResettable = false;
            } else {
                $this->isObjResettable = true;

                $obj    = $this->form()->obj();
                $method = [ $obj, 'isResettable' ];
                if (is_callable($method)) {
                    $this->isObjResettable = call_user_func($method);
                }
            }
        }

        return $this->isObjResettable;
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
        if ($this->isObjSavable === null) {
            // Overridden by permissions
            if (!$this->checkPermission('save') || !$this->form()) {
                $this->isObjSavable = false;
            } else {
                $this->isObjSavable = true;

                $obj    = $this->form()->obj();
                $method = [ $obj, 'isSavable' ];
                if (is_callable($method)) {
                    $this->isObjSavable = call_user_func($method);
                }
            }
        }

        return $this->isObjSavable;
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
        if ($this->isObjViewable === null) {
            // Overridden by permissions
            if (!$this->checkPermission('view') || !$this->form()) {
                $this->isObjViewable = false;
            } else {
                $obj = $this->form()->obj();
                $this->isObjViewable = !!$obj->id();

                $method = [ $obj, 'isViewable' ];
                if (is_callable($method)) {
                    $this->isObjViewable = call_user_func($method);
                }
            }
        }

        return $this->isObjViewable;
    }

    /**
     * Show/hide the widget's title.
     *
     * @param boolean $show Show (TRUE) or hide (FALSE) the title.
     * @return UiItemInterface Chainable
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
     * @param mixed $title The sidebar title.
     * @return FormSidebarWidget Chainable
     */
    public function setTitle($title)
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * @return Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Actions'));
        }

        return $this->title;
    }

    /**
     * @param boolean $show The show subtitle flag.
     * @return FormSidebarWidget Chainable
     */
    public function setShowSubtitle($show)
    {
        $this->showSubtitle = !!$show;
        return $this;
    }

    /**
     * @return boolean
     */
    public function showSubtitle()
    {
        if ($this->showSubtitle === false) {
            return false;
        } else {
            return !!$this->subtitle();
        }
    }

    /**
     * @param mixed $subtitle The sidebar widget subtitle.
     * @return FormSidebarWidget Chainable
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $this->translator()->translation($subtitle);

        return $this;
    }

    /**
     * @return Translation|string|null
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * Determine if the sidebar's footer is visible.
     *
     * @return boolean
     */
    public function showFooter()
    {
        // Overridden by permissions
        if (!$this->checkPermission('footer')) {
            return false;
        }

        // Overridden by conditionals
        if (!$this->isObjDeletable() && !$this->isObjResettable() &&!$this->isObjRevisionable()) {
            return false;
        }

        return $this->showFooter;
    }

    /**
     * Enable / Disable the sidebar's footer.
     *
     * @param  mixed $show The show footer flag.
     * @return FormSidebarWidget
     */
    public function setShowFooter($show)
    {
        $this->showFooter = !!$show;

        return $this;
    }

    /**
     * Determine if wrapper containing the subtitle and properties should be displayed.
     *
     * @return boolean
     */
    public function showPropertiesWrapper()
    {
        return $this->showSubtitle() || $this->hasSidebarProperties();
    }

    /**
     * Determine if wrapper containing the language switcher and actions should be displayed.
     *
     * @return boolean
     */
    public function showActionsWrapper()
    {
        return $this->showLanguageSwitch() || $this->showSidebarActions();
    }

    /**
     * @see    HasLanguageSwitcherTrait::showLanguageSwitch()
     * @return boolean
     */
    protected function resolveShowLanguageSwitch()
    {
        $form = $this->form();
        if ($form) {
            if ($form instanceof FormWidget) {
                return $form->hasL10nFormProperties();
            }
        }

        return false;
    }

    /**
     * Parse the widget's conditional logic.
     *
     * @see    AdminWidget::resolveConditionalLogic()
     * @param  callable|string $condition The callable or renderable condition.
     * @return boolean
     */
    protected function resolveConditionalLogic($condition)
    {
        $renderer = $this->getActionRenderer();
        if ($renderer && is_callable([ $renderer, $condition ])) {
            return !!$renderer->{$condition}();
        } elseif (is_callable([ $this, $condition ])) {
            return !!$this->{$condition}();
        } elseif (is_callable($condition)) {
            return !!$condition();
        } elseif ($renderer) {
            return !!$renderer->renderTemplate($condition);
        } elseif ($this->view()) {
            return !!$this->renderTemplate($condition);
        }

        return !!$condition;
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
        if (isset($this->requiredGlobalAclPermissions[$permissionName])) {
            $permissions = $this->requiredGlobalAclPermissions[$permissionName];
        } elseif (isset($this->requiredAclPermissions()[$permissionName])) {
            $permissions = $this->requiredAclPermissions()[$permissionName];
        } else {
            return true;
        }

        $authUser = $this->authenticator()->user();
        if (!$authUser || !$this->authorizer()->userAllowed($authUser, $permissions)) {
            return false;
        }

        return true;
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
