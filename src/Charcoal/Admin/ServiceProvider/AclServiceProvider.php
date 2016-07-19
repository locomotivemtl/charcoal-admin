<?php

namespace Charcoal\Admin\ServiceProvider;

// Dependencies from `ext-pdo`
use \PDO;
use \PDOException;

// Dependencies from `pimple/pimple`
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// Dependencies from `zendframework/zend-permissions`
use \Zend\Permissions\Acl\Acl;
use \Zend\Permissions\Acl\Role\GenericRole as Role;
use \Zend\Permissions\Acl\Resource\GenericResource as Resource;

/**
 * Admin ACL (Access-Control-List) provider.
 *
 * Like all service providers, this class is intended to be registered on a (Pimple) container.
 *
 * ## Services
 *
 * - `admin/acl` A Zend ACL instance containing the admin resources / permissions.
 *
 * ## Helpers
 *
 * - `admin/acl/roles`
 * - `admin/acl/permissions`
 */
class AclServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param Container $container Pimple DI container
         * @return array
         */
        $container['admin/acl/roles'] = function (Container $container) {

            $db = $container['database'];

            $q = '
            select
                `ident`,
                `parent`
            from
                `charcoal_admin_acl_roles`
            order by
                `position` asc';

            $container['logger']->debug($q);

            // Put inside a try-catch block because ACL is optional; table might not exist.
            try {
                $sth = $db->query($q);
                $roles = $sth->fetchAll(PDO::FETCH_KEY_PAIR);
            } catch (PDOException $e) {
                $container['logger']->warning('Can not fetch ACL roles: '.$e->getMessage());
                $roles = [];
            }

            return $roles;
        };

        /**
         * @param Container $container Pimple DI container
         * @return array
         */
        $container['admin/acl/permissions'] = function (Container $container) {

            $db = $container['database'];

            $q = '
            select
                `ident`,
                `denied`,
                `allowed`,
                `superuser`
            from
                `charcoal_admin_acl_roles`
            order by
                `position` asc';

            $container['logger']->debug($q);

            $permissions = [];
            // Put inside a try-catch block because ACL is optional; table might not exist.
            try {
                $sth = $db->query($q);
                while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $ident = $row['ident'];
                    if ($row['superuser']) {
                        $permissions[$ident] = 'superuser';
                    } else {
                        $allowed = explode(',', trim($row['allowed']));
                        $denied = explode(',', trim($row['denied']));
                        $permissions[$ident] = [
                            'allow' => $allowed,
                            'deny'  => $denied
                        ];
                    }
                }
            } catch (PDOException $e) {
                $container['logger']->warning('Can not fetch ACL roles: '.$e->getMessage());
                $permissions = [];
            }

            return $permissions;
        };

        /**
         * @param Container $container Pimple DI container
         * @return Acl
         */
        $container['admin/acl'] = function (Container $container) {
            $acl = new Acl();

            // Default role, which will contain default permissions
            $acl->addRole(new Role('default'));

            // Add roles
            $roles = $container['admin/acl/roles'];
            foreach ($roles as $role => $parentRole) {
                if ($parentRole) {
                    $acl->addRole(new Role($role), $parentRole);
                } else {
                    $acl->addRole(new Role($role), 'default');
                }
            }

            // Add resources
            $acl->addResource(new Resource('admin'));

            // Setup default permissions (from admin config)
            $adminConfig = $container['admin/config'];
            $defaultPermissions = $adminConfig['acl.default_permissions'];
            foreach ($defaultPermissions['allowed'] as $allowed) {
                $acl->allow('default', 'admin', $allowed);
            }
            foreach ($defaultPermissions['denied'] as $denied) {
                $acl->deny('default', 'admin', $denied);
            }

            // Add permissions
            $permissions = $container['admin/acl/permissions'];
            foreach ($permissions as $role => $permissions) {
                if ($permissions === 'superuser') {
                    // Allow all
                    $acl->allow($role);
                } else {
                    foreach ($permissions['allow'] as $allowed) {
                        $acl->allow($role, 'admin', $allowed);
                    }
                    foreach ($permissions['deny'] as $denied) {
                        $acl->deny($role, 'admin', $denied);
                    }
                }
            }

            return $acl;
        };
    }
}
