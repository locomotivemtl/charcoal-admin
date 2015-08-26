<?php

namespace Charcoal\Admin;

// From `charcoal-core`
use \Charcoal\Config\AbstractConfig as AbstractConfig;

class UserConfig extends AbstractConfig
{
    /**
    * @var array $subscription_email
    */
    private $_subscription_email;

    private $_subscription_sms;
    private $_subscription_voice;

    /**
    * @var array $lost_password_email
    */
    private $_lost_password_email;

    /**
    * @todo Integrate $data merge
    * @param array $data
    * @return UserConfig Chainable
    */
    public function set_data(array $data)
    {
        return $this;
    }
}
