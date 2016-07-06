<?php

namespace Charcoal\Admin\Widget\FormGroup;

use \Pimple\Container;

use \Zend\Permissions\Acl\Acl;
use \Zend\Permissions\Acl\Role\GenericRole as Role;
use \Zend\Permissions\Acl\Resource\GenericResource as Resource;

use \Charcoal\Admin\AdminWidget;
use \Charcoal\Admin\User\Permission;
use \Charcoal\Admin\User\PermissionCategory;

use \Charcoal\Ui\FormGroup\FormGroupInterface;
use \Charcoal\Ui\FormGroup\FormGroupTrait;

/**
 * ACL Permissions Widget (Form Group)
 */
class AclPermissions extends AdminWidget implements
    FormGroupInterface
{
    use FormGroupTrait;

    /**
     * @var Acl $roleAcl
     */
    private $roleAcl;

    /**
     * @var array
     */
    private $roleAllowed;

    /**
     * @var array
     */
    private $roleDenied;

    /**
     * @return string
     */
    public function objId()
    {
        return $_GET['obj_id'];
    }

    /**
     * @return Acl
     */
    protected function adminAcl()
    {
        return \Charcoal\App\App::instance()->getContainer()->get('admin/acl');
    }

    /**
     * @return Acl
     */
    protected function roleAcl()
    {
        if (!$this->roleAcl) {
            $id = $this->objId();

            $this->roleAcl = new Acl();
            $this->roleAcl->addRole(new Role($id));
            $this->roleAcl->addResource(new Resource('admin'));

            $q = '
            select
                `denied`,
                `allowed`,
                `superuser`
            from
                `charcoal_admin_acl_roles`
            where
                ident = :id';

            $db = \Charcoal\App\App::instance()->getContainer()->get('database');
            $sth = $db->prepare($q);
            $sth->bindParam(':id', $id);
            $sth->execute();
            $permissions = $sth->fetch(\PDO::FETCH_ASSOC);
            $this->roleAllowed = explode(',', trim($permissions['allowed']));
            $this->roleDenied = explode(',', trim($permissions['denied']));
            foreach ($this->roleAllowed as $allowed) {
                $this->roleAcl->allow($id, 'admin', $allowed);
            }
            foreach ($this->roleDenied as $denied) {
                $this->roleAcl->deny($id, 'admin', $denied);
            }
        }
        return $this->roleAcl;
    }

    /**
     * @return array
     */
    public function permissionCategories()
    {
        $factory = \Charcoal\App\App::instance()->getContainer()->get('model/factory');
        $loader = new \Charcoal\Loader\CollectionLoader([
            'logger' => $this->logger,
            'factory' => $factory
        ]);
        $model = $factory->create(PermissionCategory::class);
        $loader->setModel($model);
        $categories = $loader->load();

        $ret = [];
        foreach ($categories as $c) {
            $ret[] = [
                'ident'=>$c->id(),
                'name'=>$c->name(),
                'permissions'=>$this->loadCategoryPermissions($c->id())
            ];
        }

        return $ret;
    }

    /**
     * @param string $category The category ident to load permissions from.
     * @return array
     */
    private function loadCategoryPermissions($category)
    {
        $factory = \Charcoal\App\App::instance()->getContainer()->get('model/factory');

        $adminAcl = $this->adminAcl();
        $roleAcl = $this->roleAcl();

        $loader = new \Charcoal\Loader\CollectionLoader([
            'logger' => $this->logger,
            'factory' => $factory
        ]);
        $model = $factory->create(Permission::class);
        $loader->setModel($model);
        $loader->addFilter('category', $category);
        $permissions = $loader->load();

        $ret = [];
        foreach ($permissions as $perm) {
            $ident = $perm->id();

            $permission = [
                'ident' => $ident,
                'name' => $perm->name()
            ];
            if (in_array($ident, $this->roleAllowed)) {
                $permission['status'] = 'allowed';
            } elseif (in_array($ident, $this->roleDenied)) {
                $permission['status'] = 'denied';
            } else {
                $permission['status'] = '';
            }
            if ($adminAcl->hasResource($ident)) {
                $permission['parent_status'] = $adminAcl->isAllowed($ident, 'admin') ? 'allowed' : 'denied';
            } else {
                $permission['parent_status'] = '';
            }
            $ret[] = $permission;
        }

        return $ret;

    }
}
