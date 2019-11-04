<?php

namespace Charcoal\Admin\User;

// From 'charcoal-user'
use Charcoal\User\AuthTokenMetadata as BaseAuthTokenMetadata;

/**
 * Admin Authorization Token Metadata
 */
class AuthTokenMetadata extends BaseAuthTokenMetadata
{
    /**
     * @return array
     */
    public function defaults()
    {
        $parentDefaults = parent::defaults();

        $defaults = array_replace_recursive($parentDefaults, [
            'cookie_name' => 'charcoal_admin_login',
        ]);
        return $defaults;
    }
}
