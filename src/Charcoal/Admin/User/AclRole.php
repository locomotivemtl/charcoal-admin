<?php

namespace Charcoal\Admin\User;

use \Charcoal\User\AclRole as UserAclRole;

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
