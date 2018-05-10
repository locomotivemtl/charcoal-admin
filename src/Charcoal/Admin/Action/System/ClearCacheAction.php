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
        } elseif ($cacheType === 'cdn') {
            if (!$this->purgeCdnCache()) {
                $this->setSuccess(false);
                return $response->withStatus(400);
            }
            $this->addFeedback('success', $this->translator()->translate('Cdn cache cleared successfully.'));
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

    private function purgeCdnCache()
    {
        $cdnConfig = $this->appConfig('cdn');

        if (!$cdnConfig) {
            $this->addFeedback('error', $this->translator()->translate('No cdn config found'));
            return false;
        }

        if ($cdnConfig) {
            $type = $cdnConfig['type'];

            switch ($type) {
                case 'cloudflare':
                    return $this->purgeCloudflareCache();
                    break;
                default:
                    $this->addFeedback('error', $this->translator()->translate(
                        sprintf('The cdn type "%s" is not supported', $type)
                    ));
                    return false;
            }
        }
    }

    private function purgeCloudflareCache()
    {
        $api = $this->appConfig('apis.cloudflare');

        if (!$api) {
            $this->addFeedback('error', $this->translator()->translate(
                sprintf('No api config found for "%s" cdn', 'cloudflare')
            ));
            return false;
        }

        $url = 'https://api.cloudflare.com/client/v4/zones/023e105f4ecef8ad9ca31a8372d0c353/purge_cache';
        $header = [
            'Content-Type: application/json',
            sprintf('X-Auth-Email: %s', $api['auth_email']),
            sprintf('X-Auth-Key: %s', $api['auth_key']),
        ];
        $fields = [
            'purge_everything' => true
        ];

        //open connection
        $ch = curl_init();

        //set options
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //needed so that the $result=curl_exec() output is the file and isn't just true/false

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        return $result['success'];
    }
}
