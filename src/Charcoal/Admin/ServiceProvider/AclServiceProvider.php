<?php

namespace Charcoal\Admin\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;

// From 'zendframework/zend-permissions'
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as AclResource;

// From 'charcoal-user'
use Charcoal\User\Acl\Manager as AclManager;

/**
 * Admin ACL (Access-Control-List) provider.
 *
 * Like all service providers, this class is intended to be registered on a (Pimple) container.
 *
 * ## Services
 *
 * - `admin/acl` A Zend ACL instance containing the admin resources / permissions.
 *
 * ## Dependencies
 *
 * This service provider expects a few "global" services to be registered on the container:
 * - `logger`, a PSR-3 logger
 * - `database`, a PDO instance
 * - `admin/config`, a PSR-6 Cache pool
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
         * Use an AclManager to load default permissions from config and database.
         *
         * @param Container $container Pimple DI container
         * @return Acl
         */
        $container['admin/acl'] = function (Container $container) {

            $adminConfig = $container['admin/config'];

            $resourceName = 'admin';
            $tableName = 'charcoal_admin_acl_roles';

            $aclManager = new AclManager([
                'logger' => $container['logger']
            ]);

            $acl = new Acl();

             // Add admin resource for ACL
            $acl->addResource(new AclResource($resourceName));

            // Setup default permissions (from admin config)
            $permissions = $adminConfig['acl.permissions'];
            if (!empty($permissions)) {
                $aclManager->loadPermissions($acl, $permissions, $resourceName);
            }

            // Setup roles and permissions from database
            $aclManager->loadDatabasePermissions($acl, $container['database'], $tableName, $resourceName);

            return $acl;
        };
    }
}
