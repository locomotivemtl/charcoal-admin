<?php

declare(strict_types=1);

namespace Charcoal\Admin\Action\System;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Action\System\AbstractCacheAction;

/**
 * Purge stale or expired items.
 */
class PurgeCacheAction extends AbstractCacheAction
{
    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $cache  = $this->cachePool();
        $result = $cache->purge();

        if ($result) {
            $message = $this->translator()->trans('Cache purged successfully.');
        } else {
            $message = $this->translator()->trans('Failed to purge cache.');
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
}
