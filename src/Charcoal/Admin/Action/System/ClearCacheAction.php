<?php

declare(strict_types=1);

namespace Charcoal\Admin\Action\System;

use Charcoal\Admin\Action\System\AbstractCacheAction;
use Charcoal\Cache\Information\PoolInformationInterface;
use Charcoal\Cache\Information\PoolInformationFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Empty the entire cache pool of items.
 */
class ClearCacheAction extends AbstractCacheAction
{
    /**
     * The "default" item pool.
     *
     * @var string
     */
    public const CACHE_POOL_DEFAULT = 'default';

    /**
     * Deprecated "global" item key group.
     *
     * @var string
     */
    public const CACHE_TYPE_GLOBAL = 'global';

    /**
     * Deprecated "objects" item key group.
     *
     * @var string
     */
    public const CACHE_TYPE_OBJECTS = 'objects';

    /**
     * Deprecated "requests" item key group.
     *
     * @var string
     */
    public const CACHE_TYPE_REQUESTS = 'requests';

    /**
     * Deprecated: The cache item type ("objects" or "requests").
     *
     * @var string
     */
    public const QUERY_PARAM_CACHE_TYPE = 'cache_type';

    /**
     * The cache item pool query parameter.
     *
     * @var string
     */
    public const QUERY_PARAM_CACHE_ITEM_POOL = 'cache_item_pool';

    /**
     * The cache item keys query parameter.
     *
     * @var string
     */
    public const QUERY_PARAM_CACHE_ITEM_KEYS = 'cache_item_keys';

    /**
     * @todo   Implement support for deleting a specific cache item.
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $translator = $this->translator();

        $cacheItemPool = $this->getCacheItemPoolFromRequestOrDefaultPool($request);
        if ($cacheItemPool === null) {
            $this->addFeedback('error', $translator->trans('Cache item pool not defined.'));
            $this->setSuccess(false);
            return $response->withStatus(400);
        }

        $cacheItemKeys = $this->getCacheItemKeysFromRequest($request, $cacheItemPool);
        if ($cacheItemKeys === null) {
            $this->addFeedback('error', $translator->trans('Cache item keys not defined.'));
            $this->setSuccess(false);
            return $response->withStatus(400);
        }

        $result  = false;
        $message = null;

        if ($cacheItemKeys) {
            $result = $cacheItemPool->deleteItems($cacheItemKeys);

            if ($result) {
                $message = $translator->trans('Cache items deleted successfully.');
            } else {
                $message = $translator->trans('Failed to delete cache items.');
            }
        } else {
            $result = $cacheItemPool->clear();

            if ($result) {
                $message = $translator->trans('Cache cleared successfully.');
            } else {
                $message = $translator->trans('Failed to clear cache.');
            }
        }

        $this->setSuccess($result);

        if ($result) {
            $this->addFeedback('success', $message);
            return $response;
        } else {
            $this->addFeedback('error', $message);
            return $response->withStatus(500);
        }
    }

    /**
     * Extracts the cache item pool from the HTTP request,
     * or uses the default cache item pool.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return CacheItemPoolInterface
     */
    protected function getCacheItemPoolFromRequestOrDefaultPool(RequestInterface $request): CacheItemPoolInterface
    {
        return ($this->getCacheItemPoolFromRequest($request) ?? $this->cachePool());
    }

    /**
     * Extracts the cache item pool from the HTTP request.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return ?CacheItemPoolInterface
     */
    protected function getCacheItemPoolFromRequest(RequestInterface $request): ?CacheItemPoolInterface
    {
        $poolName = $request->getParam(self::QUERY_PARAM_CACHE_ITEM_POOL);

        if (!ctype_alnum($poolName)) {
            return null;
        }

        /** @todo Implement support for cache pools */
        return null;
    }

    /**
     * Extracts the cache item keys from the HTTP request.
     *
     * @param  RequestInterface       $request       A PSR-7 compatible Request instance.
     * @param  CacheItemPoolInterface $cacheItemPool A PSR-6 compatible Cache Item Pool instance.
     * @return ?(string[])
     */
    protected function getCacheItemKeysFromRequest(
        RequestInterface $request,
        CacheItemPoolInterface $cacheItemPool
    ): ?array {
        $group = $request->getParam(self::QUERY_PARAM_CACHE_TYPE);
        if ($group) {
            trigger_error(
                sprintf(
                    '[%1$s] Query parameter "cache_type" is deprecated since %2$s. '.
                    'Use "cache_preset" or "cache_key" instead.',
                    get_called_class(),
                    '0.31.0'
                ),
                E_USER_DEPRECATED
            );

            $group = $cacheInfo->filterItemKey($group);

            switch ($group) {
                case self::CACHE_TYPE_GLOBAL:
                    return [];

                case self::CACHE_TYPE_OBJECTS:
                    return [
                        'object',
                        'metadata',
                    ];

                case self::CACHE_TYPE_REQUESTS:
                    return [
                        'request',
                        'template',
                    ];
            }

            return null;
        }

        $cacheInfo = PoolInformationFactory::create($cacheItemPool);

        $keys = $request->getParam(self::QUERY_PARAM_CACHE_ITEM_KEYS);
        if (is_string($keys)) {
            return $cacheInfo->filterItemKey($keys);
        } elseif (is_array($keys)) {
            $keys = $cacheInfo->filterItemKeys($keys);
            if ($keys) {
                return $keys;
            }
        }

        return null;
    }
}
