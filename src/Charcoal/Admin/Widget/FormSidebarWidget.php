<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

// From Pimple
use \Pimple\Container;

// From 'charcoal-translation'
use \Charcoal\Translation\TranslationString;
use \Charcoal\Translation\TranslationConfig;

// From 'charcoal-ui'
use \Charcoal\Ui\Form\FormInterface;

// From 'charcoal-admin'
use \Charcoal\Admin\AdminWidget;
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
    use AuthAwareTrait;

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
     * @var Object $actions
     */
    private $actions;

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
     * @var TranslationString $title
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
    public function setData($data)
    {
        parent::setData($data);

        if (isset($data['properties']) && $data['properties'] !== null) {
            $this->setSidebarProperties($data['properties']);
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
        if (TranslationString::isTranslatable($subtitle)) {
            $this->title = new TranslationString($subtitle);
        }

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
     * Defined the form actions.
     * @param object $actions The sidebar actions.
     * @return FormSidebarWidget Chainable
     */
    public function setActions($actions)
    {
        //todo : include the default save action.

        if (!$actions) {
            return $this;
        }
        $this->actions = [];

        foreach ($actions as $ident => $action) {
            if (!isset($action['url']) || !isset($action['label'])) {
                continue;
            }

            if (!TranslationString::isTranslatable($action['label'])) {
                continue;
            }

            $label     = new TranslationString($action['label']);
            $condition = (isset($action['condition']) ? $action['condition'] : true);

            $obj = $this->form()->obj();
            // Shame: Make sure the view is set before attempt rendering
            if ($obj->view()) {
                if (!is_bool($condition)) {
                    $condition = $obj->render($condition);
                }
                $url = $obj->render($action['url']);
            } else {
                // Shame part 2: force '{{id}}' to use obj_id GET parameter...
                if (isset($_GET['obj_id'])) {
                    $url = preg_replace('~\{\{\s*(obj_)?id\s*\}\}~', $_GET['obj_id'], $action['url']);
                } else {
                    $url = $action['url'];
                }
            }

            // Info = default
            // Possible: danger, info
            $btn             = (isset($action['type']) ? $action['type'] : 'info');
            $this->actions[] = [
                'label'       => $label,
                'url'         => $url,
                'button_type' => $btn,
                'active'      => $condition
            ];
        }

        return $this;
    }

    /**
     * Returns the actions as an ArrayIterator
     * [ ['label' => $label, 'url' => $url] ]
     * @see $this->set_actions()
     * @return object actions
     */
    public function actions()
    {
        return $this->actions;
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
        $method = [$obj, 'isDeletable'];

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
        $method = [$obj, 'isResettable'];

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
        $method = [$obj, 'isSavable'];

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
        if (TranslationString::isTranslatable($title)) {
            $this->title = new TranslationString($title);
        }

        return $this;
    }

    /**
     * @return TranslationString
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

    // ==========================================================================
    // PERMISSIONS
    // ==========================================================================

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

    // ==========================================================================
    // UTILS
    // ==========================================================================

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
