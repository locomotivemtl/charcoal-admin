<?php

namespace Charcoal\Admin\Template\System;

use APCUIterator;
use APCIterator;
use RuntimeException;

use Stash\Driver\Apc;
use Stash\Driver\Memcache;

use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 * Cache information.
 */
class ClearCacheTemplate extends AdminTemplate
{
    /**
     * Cache service.
     *
     * @var \Stash\Pool
     */
    private $cache;

    /**
     * Summary of cache.
     *
     * @var array
     */
    private $cacheInfo;

    /**
     * Cache service config.
     *
     * @var \Charcoal\App\Config\CacheConfig
     */
    private $cacheConfig;

    /**
     * Driver Name => Class Name.
     *
     * @var \Stash\Interfaces\DriverInterface
     */
    private $cacheDriver;

    /**
     * Driver Name => Class Name.
     *
     * @var array
     */
    private $availableCacheDrivers;

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Cache information'));
        }

        return $this->title;
    }

    /**
     * @return \Charcoal\Admin\Widget\SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        if ($this->sidemenu === null) {
            $this->sidemenu = $this->createSidemenu('system');
        }

        return $this->sidemenu;
    }

    /**
     * @param  boolean $force Whether to reload cache information.
     * @return array
     */
    public function cacheInfo($force = false)
    {
        if ($this->cacheInfo === null || $force === true) {
            $flip      = array_flip($this->availableCacheDrivers);
            $driver    = get_class($this->cache->getDriver());
            $cacheType = isset($flip['\\'.$driver]) ? $flip['\\'.$driver] : $driver;

            # $globalItems = $this->globalCacheItems();
            $pageItems   = $this->pagesCacheItems();
            $objectItems = $this->objectsCacheItems();
            $this->cacheInfo = [
                'type'              => $cacheType,
                'active'            => $this->cacheConfig['active'],
                'global'            => $this->globalCacheInfo(),
                'pages'             => $this->pagesCacheInfo(),
                'objects'           => $this->objectsCacheInfo(),
                # 'global_items'      => $globalItems,
                'pages_items'       => $pageItems,
                'objects_items'     => $objectItems,
                # 'num_global_items'  => count($globalItems),
                'num_pages_items'   => count($pageItems),
                'num_objects_items' => count($objectItems),
            ];
        }

        return $this->cacheInfo;
    }

    /**
     * @return string
     */
    private function getGlobalCacheKey()
    {
        return '/::'.$this->cache->getNamespace().'::/';
    }

    /**
     * @return array
     */
    private function globalCacheInfo()
    {
        if ($this->isApc()) {
            $cacheKey = $this->getGlobalCacheKey();
            return $this->apcCacheInfo($cacheKey);
        } else {
            return [
                'num_entries'  => 0,
                'total_size'   => 0,
                'average_size' => 0,
                'total_hits'   => 0,
                'average_hits' => 0,
            ];
        }
    }

    /**
     * @return array
     */
    private function globalCacheItems()
    {
        if ($this->isApc()) {
            $cacheKey = $this->getGlobalCacheKey();
            return $this->apcCacheItems($cacheKey);
        } else {
            return [];
        }
    }

    /**
     * @return string
     */
    private function getPagesCacheKey()
    {
        return '/::'.$this->cache->getNamespace().'::request::|::'.$this->cache->getNamespace().'::template::/';
    }

    /**
     * @return array
     */
    private function pagesCacheInfo()
    {
        if ($this->isApc()) {
            $cacheKey = $this->getPagesCacheKey();
            return $this->apcCacheInfo($cacheKey);
        } else {
            return [
                'num_entries'  => 0,
                'total_size'   => 0,
                'average_size' => 0,
                'total_hits'   => 0,
                'average_hits' => 0,
            ];
        }
    }

    /**
     * @return array
     */
    private function pagesCacheItems()
    {
        if ($this->isApc()) {
            $cacheKey = $this->getPagesCacheKey();
            return $this->apcCacheItems($cacheKey);
        } else {
            return [];
        }
    }

    /**
     * @return string
     */
    private function getObjectsCacheKey()
    {
        return '/::'.$this->cache->getNamespace().'::object::|::'.$this->cache->getNamespace().'::metadata::/';
    }

    /**
     * @return array
     */
    private function objectsCacheInfo()
    {
        if ($this->isApc()) {
            $cacheKey = $this->getObjectsCacheKey();
            return $this->apcCacheInfo($cacheKey);
        } else {
            return [
                'num_entries'  => 0,
                'total_size'   => 0,
                'average_size' => 0,
                'total_hits'   => 0,
                'average_hits' => 0,
            ];
        }
    }

    /**
     * @return array
     */
    private function objectsCacheItems()
    {
        if ($this->isApc()) {
            $cacheKey = $this->getObjectsCacheKey();
            return $this->apcCacheItems($cacheKey);
        } else {
            return [];
        }
    }

    /**
     * @param  string $key The cache key to look at.
     * @return array
     */
    private function apcCacheInfo($key)
    {
        $iter = $this->createApcIterator($key);

        $numEntries = 0;
        $sizeTotal  = 0;
        $hitsTotal  = 0;
        $ttlTotal   = 0;
        foreach ($iter as $item) {
            $numEntries++;
            $sizeTotal += $item['mem_size'];
            $hitsTotal += $item['num_hits'];
            $ttlTotal  += $item['ttl'];
        }
        $sizeAvg = $numEntries ? ($sizeTotal / $numEntries) : 0;
        $hitsAvg = $numEntries ? ($hitsTotal / $numEntries) : 0;
        return [
            'num_entries'  => $numEntries,
            'total_size'   => $this->formatBytes($sizeTotal),
            'average_size' => $this->formatBytes($sizeAvg),
            'total_hits'   => $hitsTotal,
            'average_hits' => $hitsAvg,
        ];
    }

    /**
     * @param  string $key The cache key to look at.
     * @return array|\Generator
     */
    private function apcCacheItems($key)
    {
        $iter = $this->createApcIterator($key);

        foreach ($iter as $item) {
            $item['ident']   = $this->formatApcCacheKey($item['key']);
            $item['size']    = $this->formatBytes($item['mem_size']);
            $item['created'] = date('Y-m-d H:i:s', $item['creation_time']);
            $item['expiry']  = date('Y-m-d H:i:s', ($item['creation_time']+$item['ttl']));
            yield $item;
        }
    }

    /**
     * @param  string $key The cache item key to load.
     * @throws RuntimeException If the APC Iterator class is missing.
     * @return \APCIterator|\APCUIterator|null
     */
    private function createApcIterator($key)
    {
        if (class_exists('\\APCUIterator', false)) {
            return new \APCUIterator($key);
        } elseif (class_exists('\\APCIterator', false)) {
            return new \APCIterator('user', $key);
        } else {
            throw new RuntimeException('Cache uses APC but no iterator could be found.');
        }
    }

    /**
     * @return boolean
     */
    private function isApc()
    {
        return is_a($this->cache->getDriver(), Apc::class);
    }

    /**
     * @return boolean
     */
    private function isMemcache()
    {
        return is_a($this->cache->getDriver(), Memcache::class);
    }

    /**
     * Human-readable identifier format.
     *
     * @param  string $key The cache item key to format.
     * @return string
     */
    private function formatApcCacheKey($key)
    {
        $nss = $this->cache->getNamespace();
        $key = str_replace($nss, '', strstr($key, $nss.'::'));
        $key = preg_replace([ '/:+/', '/\.+/' ], [ '⇒', '/' ], trim($key, ':'));
        return $key;
    }

    /**
     * Human-readable bytes format.
     *
     * @param  integer $bytes The number of bytes to format.
     * @return string
     */
    private function formatBytes($bytes)
    {
        if ($bytes === 0) {
            return 0;
        }

        $units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
        $base  = log($bytes, 1024);
        $floor = floor($base);
        $unit  = $units[$floor];
        $size  = round(pow(1024, ($base - $floor)), 2);

        $locale = localeconv();
        $size   = number_format($size, 2, $locale['decimal_point'], $locale['thousands_sep']);

        return rtrim($size, '.0').' '.$unit;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->availableCacheDrivers = $container['cache/available-drivers'];
        $this->cacheDriver           = $container['cache/driver'];
        $this->cache                 = $container['cache'];
        $this->cacheConfig           = $container['cache/config'];
    }
}
