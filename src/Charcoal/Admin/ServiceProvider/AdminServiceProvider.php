<?php

namespace Charcoal\Admin\ServiceProvider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

use \Charcoal\App\Template\WidgetFactory;

/**
 *
 */
class AdminServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['admin/widget/factory'] = function (Container $container) {
        
            // The admin widget factory is a standard widget factory.
            $factory = new WidgetFactory();
        };
    }
}
