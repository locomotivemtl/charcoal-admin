<?php

namespace Charcoal\Admin\User;

// From 'charcoal-user'
use Charcoal\User\Acl\Role as UserAclRole;

/**
 * ACL Role, to manage permissions
 */
class AclRole extends UserAclRole
{
    /**
     * Override key to ident
     * Temp fix
     *
     * todo remove when metadata key can be used.
     *
     * @return string
     */
    public function key()
    {
        return 'ident';
    }
}
