<?php

namespace Charccoal\Admin;

// From `charcoal-core`
use \Charcoal\Config\AbstractConfig as AbstractConfig;

class UserConfig extends AbstractConfig
{
    /**
    * @var array $subscription_email
    */
    private $subscription_email;

    /**
    * @var array $lost_password_email
    */
    private $lost_password_email;
}
