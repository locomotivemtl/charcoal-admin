<?php

namespace Charcoal\Admin\Action\System;

// From PSR-6
use \Psr\Cache\CacheItemPoolInterface;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminAction;

/**
 *
 */
class ClearCacheAction extends AdminAction
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $cacheType = $request->getParam('cache_type');

        if (!$cacheType) {
            $this->addFeedback('error', $this->translator()->translate('Cache type not defined.'));
            $this->setSuccess(false);
            return $response->withStatus(400);
        }

        if ($cacheType === 'global') {
            $this->cache->clear();
            $this->addFeedback('success', $this->translator()->translate('Cache cleared successfully.'));
            $this->setSuccess(true);
        } elseif ($cacheType === 'pages') {
            $this->cache->deleteItem('request');
            $this->cache->deleteItem('template');
            $this->addFeedback('success', $this->translator()->translate('Pages cache cleared successfully.'));
            $this->setSuccess(true);
        } elseif ($cacheType === 'objects') {
            $this->cache->deleteItem('object');
            $this->cache->deleteItem('metadata');
            $this->addFeedback('success', $this->translator()->translate('Objects cache cleared successfully.'));
            $this->setSuccess(true);
        } else {
            $this->addFeedback('error', $this->translator()->translate(sprintf('Invalid cache type "%s"', $cacheType)));
            $this->setSuccess(false);
            return $response->withStatus(400);
        }

        return $response;
    }

    /**
     * @return array
     */
    public function results()
    {
        $ret = [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];

        return $ret;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setCache($container['cache']);
    }

    /**
     * @param CacheItemPoolInterface $cache A PSR-6 cache item pool.
     * @return void
     */
    private function setCache(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }
}
