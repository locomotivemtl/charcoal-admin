<?php

namespace Charcoal\Admin\Template\System;

use APCUIterator;

use Stash\Pool;
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
     * @var Stash\Pool
     */
    private $cache;

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->cacheDriver = $container['cache/driver'];
        $this->cache = $container['cache'];
    }

    /**
     * Retrieve the title of the page.
     *
     * @return Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Clear cache'));
        }

        return $this->title;
    }

    /**
     * @return SidemenuWidgetInterface|null
     */
    public function sidemenu()
    {
        return $this->createSidemenu('system');
    }


    /**
     * @return array
     */
    public function cacheInfo()
    {
        return [
            'type' => get_class($this->cache->getDriver()),
            'global' => $this->globalCacheInfo(),
            'pages' => $this->pagesCacheInfo(),
            'objects' => $this->objectsCacheInfo()
        ];
    }

    /**
     * @return array
     */
    private function globalCacheInfo()
    {
        if ($this->isApc()) {
            return $this->apcCacheInfo('/::'.$this->cache->getNamespace().'::/');
        } else {
            return [
                'num_entries' => 0,
                'total_size' => 0,
                'average_size' => 0,
                'total_hits' => 0,
                'average_hits' => 0
            ];
        }
    }

    /**
     * @return array
     */
    private function pagesCacheInfo()
    {
        if ($this->isApc()) {
            return $this->apcCacheInfo('/::'.$this->cache->getNamespace().'::request::|::'.$this->cache->getNamespace().'::template::/');
        } else {
            return [
                'num_entries' => 0,
                'total_size' => 0,
                'average_size' => 0,
                'total_hits' => 0,
                'average_hits' => 0
            ];
        }
    }

    /**
     * @return array
     */
    private function objectsCacheInfo()
    {
        if ($this->isApc()) {
            return $this->apcCacheInfo('/::'.$this->cache->getNamespace().'::object::|::'.$this->cache->getNamespace().'::metadata::/');
        } else {
            return [
                'num_entries' => 0,
                'total_size' => 0,
                'average_size' => 0,
                'total_hits' => 0,
                'average_hits' => 0
            ];
        }
    }

    /**
     * @param string $key The cache key to look at.
     * @return array
     */
    private function apcCacheInfo($key)
    {
        $iter = new APCUIterator($key);
        $numEntries = 0;
        $sizeTotal = 0;
        $hitsTotal = 0;
        $ttlTotal = 0;
        foreach ($iter as $item) {
            $numEntries++;
            $sizeTotal += $item['mem_size'];
            $hitsTotal += $item['num_hits'];
            $ttlTotal += $item['ttl'];

        }
        $sizeAvg = $numEntries ? ($sizeTotal / $numEntries) : 0;
        $hitsAvg = $numEntries ? ($hitsTotal / $numEntries) : 0;
        return [
            'num_entries' => $numEntries,
            'total_size' => $this->formatBytes($sizeTotal),
            'average_size' => $this->formatBytes($sizeAvg),
            'total_hits' => $hitsTotal,
            'average_hits' => $hitsAvg
        ];
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
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        $floor = floor($base);
        return round(pow(1024, ($base - $floor)), 2).' '.$suffixes[$floor];
    }
}
