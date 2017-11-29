<?php

namespace Charcoal\Admin\Template\System;

use APCUIterator;
use APCIterator;

use Stash\Driver\Apc;
use Stash\Driver\Memcache;

use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 *
 */
class ClearCacheTemplate extends AdminTemplate
{
    /**
     * @var \Stash\Pool
     */
    private $cache;

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->availableCacheDrivers = $container['cache/available-drivers'];
        $this->cacheDriver = $container['cache/driver'];
        $this->cache = $container['cache'];
        $this->cacheConfig = $container['cache/config'];
    }

    /**
     * Retrieve the title of the page.
     *
     * @return Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Cache information'));
        }

        return $this->title;
    }

    /**
     * @return SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        if ($this->sidemenu === null) {
            $this->sidemenu = $this->createSidemenu('system');
        }

        return $this->sidemenu;
    }


    /**
     * @return array
     */
    public function cacheInfo()
    {
        $flip = array_flip($this->availableCacheDrivers);
        $driver = get_class($this->cache->getDriver());

        $cacheType = isset($flip['\\'.$driver]) ? $flip['\\'.$driver] : $driver;
        return [
            'type'    => $cacheType,
            'active'  => $this->cacheConfig['active'],
            'global'  => $this->globalCacheInfo(),
            'pages'   => $this->pagesCacheInfo(),
            'objects' => $this->objectsCacheInfo(),
            'pages_items' => $this->pagesCacheItems(),
            'objects_items' => $this->objectsCacheItems()
        ];
    }

    /**
     * @return array
     */
    private function globalCacheInfo()
    {
        if ($this->isApc() === true) {
            return $this->apcCacheInfo('/::'.$this->cache->getNamespace().'::/');
        } else {
            return [
                'num_entries'  => 0,
                'total_size'   => 0,
                'average_size' => 0,
                'total_hits'   => 0,
                'average_hits' => 0
            ];
        }
    }

    /**
     * @return array
     */
    private function pagesCacheInfo()
    {
        if ($this->isApc() === true) {
            return $this->apcCacheInfo(
                '/::'.$this->cache->getNamespace().'::request::|::'.$this->cache->getNamespace().'::template::/'
            );
        } else {
            return [
                'num_entries'  => 0,
                'total_size'   => 0,
                'average_size' => 0,
                'total_hits'   => 0,
                'average_hits' => 0
            ];
        }
    }

    /**
     * @return array
     */
    private function pagesCacheItems()
    {
        if ($this->isApc() === true) {
            return $this->apcCacheItems('/::'.$this->cache->getNamespace().'::request::|::'.$this->cache->getNamespace().'::template::/');
        } else {
            return [];
        }
    }

    /**
     * @return array
     */
    private function objectsCacheInfo()
    {
        if ($this->isApc() === true) {
            return $this->apcCacheInfo(
                '/::'.$this->cache->getNamespace().'::object::|::'.$this->cache->getNamespace().'::metadata::/'
            );
        } else {
            return [
                'num_entries'  => 0,
                'total_size'   => 0,
                'average_size' => 0,
                'total_hits'   => 0,
                'average_hits' => 0
            ];
        }
    }

    /**
     * @return array
     */
    private function objectsCacheItems()
    {
        if ($this->isApc()) {
            return $this->apcCacheItems('/::'.$this->cache->getNamespace().'::objects::|::'.$this->cache->getNamespace().'::metadata::/');
        } else {
            return [];
        }
    }

    /**
     * @param string $key The cache key to look at.
     * @return array
     */
    private function apcCacheInfo($key)
    {
        if (class_exists(APCUIterator::class)) {
            $iter = new APCUIterator($key);
        } elseif (class_exists(APCIterator::class)) {
            $iter = new APCIterator($key);
        } else {
            // Shouldn't happen.
            return [];
        }

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
            'average_hits' => $hitsAvg
        ];
    }

    /**
     * @param string $key The cache key to look at.
     * @return array|\Generator
     */
    private function apcCacheItems($key)
    {
        if (class_exists(APCUIterator::class)) {
            $iter = new APCUIterator($key);
        } elseif (class_exists(APCIterator::class)) {
            $iter = new APCIterator($key);
        } else {
            return [];
        }

        foreach ($iter as $item) {
            $item['ident'] = str_replace('::', '⇒', str_replace('.', '/', trim(str_replace($this->cache->getNamespace(), '', strstr($item['key'], $this->cache->getNamespace().'::')), ':')));
            $item['size'] = $this->formatBytes($item['mem_size']);
            $item['created'] = date('Y-m-d H:i:s', $item['creation_time']);
            $item['expiry'] = date('Y-m-d H:i:s', ($item['creation_time']+$item['ttl']));
            yield $item;
        }
    }

    /**
     * @return boolean
     */
    private function isApc()
    {
        return (get_class($this->cache->getDriver()) == Apc::class);
    }

    /**
     * @return boolean
     */
    private function isMemcache()
    {
        return (get_class($this->cache->getDriver()) == Memcache::class);
    }

    /**
     * Human-readable bytes format.
     *
     * @param integer $size The number of bytes to format.
     * @return boolean
     */
    private function formatBytes($size)
    {
        if ($size === 0) {
            return 0;
        }
        $base = log($size, 1024);
        $suffixes = [ 'b', 'k', 'M', 'G', 'T' ];

        $floor = floor($base);
        return round(pow(1024, ($base - $floor)), 2).''.$suffixes[$floor];
    }
}
