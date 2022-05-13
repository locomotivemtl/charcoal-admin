<?php

declare(strict_types=1);

namespace Charcoal\Admin\Action\System;

// From PSR-6
use Psr\Cache\CacheItemPoolInterface;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-cache'
use Charcoal\Cache\CachePoolAwareTrait;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;

/**
 * Base Cache Action
 */
abstract class AbstractCacheAction extends AdminAction
{
    use CachePoolAwareTrait;

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks(),
        ];
    }

    /**
     * Set dependencies from the service locator.
     *
     * @param  Container $container A service locator.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setCachePool($container['cache']);
    }
}
