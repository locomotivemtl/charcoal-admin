<?php

namespace Charcoal\Admin\Template\System;

use APCUIterator;
use APCIterator;
use DateInterval;
use DateTimeInterface;
use DateTime;
use RuntimeException;

use Stash\Driver\Apc;
use Stash\Driver\Ephemeral;
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
     * @var array
     */
    private $availableCacheDrivers;

    /**
     * Regular expression pattern to match a Stash / APC cache key.
     *
     * @var string
     */
    private $apcCacheKeyPattern;

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

            $globalItems = $this->globalCacheItems();
            $this->cacheInfo = [
                'type'              => $cacheType,
                'active'            => $this->cacheConfig['active'],
                'namespace'         => $this->getCacheNamespace(),
                'global'            => $this->globalCacheInfo(),
                'pages'             => $this->pagesCacheInfo(),
                'objects'           => $this->objectsCacheInfo(),
                'global_items'      => $globalItems,
                'has_global_items'  => !empty($globalItems),
            ];
        }

        return $this->cacheInfo;
    }

    /**
     * @return string
     */
    private function getCacheNamespace()
    {
        return $this->cache->getNamespace();
    }

    /**
     * @return string
     */
    private function getApcNamespace()
    {
        return $this->cacheConfig['prefix'];
    }

    /**
     * @return string
     */
    private function getGlobalCacheKey()
    {
        return '/::'.$this->getCacheNamespace().'::/';
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
        return '/::'.$this->getCacheNamespace().'::request::|::'.$this->getCacheNamespace().'::template::/';
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
        return '/::'.$this->getCacheNamespace().'::object::|::'.$this->getCacheNamespace().'::metadata::/';
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
            $item['ident'] = $this->formatApcCacheKey($item['key']);
            $item['size']  = $this->formatBytes($item['mem_size']);

            $item['expiration_time'] = ($item['creation_time'] + $item['ttl']);

            $date1 = new DateTime('@'.$item['creation_time']);
            $date2 = new DateTime('@'.$item['expiration_time']);

            $item['created'] = $date1->format('Y-m-d H:i:s');
            $item['expiry']  = $date2->format('Y-m-d H:i:s');
            $item['timeout'] = $this->formatTimeDiff($date1, $date2);
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
     * Determine if Charcoal has cache statistics.
     *
     * @return boolean
     */
    public function hasStats()
    {
        return $this->isApc();
    }

    /**
     * Determine if Charcoal is using the APC driver.
     *
     * @return boolean
     */
    public function isApc()
    {
        return is_a($this->cache->getDriver(), Apc::class);
    }

    /**
     * Determine if Charcoal is using the Memcache driver.
     *
     * @return boolean
     */
    public function isMemcache()
    {
        return is_a($this->cache->getDriver(), Memcache::class);
    }

    /**
     * Determine if Charcoal is using the Ephemeral driver.
     *
     * @return boolean
     */
    public function isMemory()
    {
        return is_a($this->cache->getDriver(), Ephemeral::class);
    }

    /**
     * Get the RegExp pattern to match a Stash / APC cache key.
     *
     * Breakdown:
     * - `apcID`: Installation ID
     * - `apcNS`: Optional. Application Key or Installation ID
     * - `stashNS`: Stash Segment
     * - `poolNS`: Optional. Application Key
     * - `appKey`: Data Segment
     *
     * @return string
     */
    private function getApcCacheKeyPattern()
    {
        if ($this->apcCacheKeyPattern === null) {
            $pattern  = '/^(?<apcID>[a-f0-9]{32})::(?:(?<apcNS>';
            $pattern .= preg_quote($this->getApcNamespace());
            $pattern .= '|[a-f0-9]{32})::)?(?<stashNS>cache|sp)::(?:(?<poolNS>';
            $pattern .= preg_quote($this->getCacheNamespace());
            $pattern .= ')::)?(?<itemID>.+)$/i';

            $this->apcCacheKeyPattern = $pattern;
        }

        return $this->apcCacheKeyPattern;
    }

    /**
     * Human-readable identifier format.
     *
     * @param  string $key The cache item key to format.
     * @return string
     */
    private function formatApcCacheKey($key)
    {
        $pattern = $this->getApcCacheKeyPattern();
        if (preg_match($pattern, $key, $matches)) {
            $sns = $matches['stashNS'];
            $iid = trim($matches['itemID'], ':');
            $iid = preg_replace([ '/:+/', '/\.+/' ], [ '⇒', '/' ], $iid);
            $key = $matches['stashNS'] . '⇒' . $iid;
        }

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
     * Human-readable time difference.
     *
     * Note: Adapted from CakePHP\Chronos.
     *
     * @see https://github.com/cakephp/chronos/blob/1.1.4/LICENSE
     *
     * @param DateTimeInterface      $date1 The datetime to start with.
     * @param DateTimeInterface|null $date2 The datetime to compare against.
     * @return string
     */
    private function formatTimeDiff(DateTimeInterface $date1, DateTimeInterface $date2 = null)
    {
        $isNow = $date2 === null;
        if ($isNow) {
            $date2 = new DateTime('now', $date1->getTimezone());
        }
        $interval = $date1->diff($date2);

        $translator = $this->translator();

        switch (true) {
            case ($interval->y > 0):
                $unit  = 'time.year';
                $count = $interval->y;
                break;
            case ($interval->m > 0):
                $unit  = 'time.month';
                $count = $interval->m;
                break;
            case ($interval->d > 0):
                $unit  = 'time.day';
                $count = $interval->d;
                if ($count >= 7) {
                    $unit  = 'time.week';
                    $count = (int)($count / 7);
                }
                break;
            case ($interval->h > 0):
                $unit  = 'time.hour';
                $count = $interval->h;
                break;
            case ($interval->i > 0):
                $unit  = 'time.minute';
                $count = $interval->i;
                break;
            default:
                $count = $interval->s;
                $unit  = 'time.second';
                break;
        }

        $time = $translator->transChoice($unit, $count, [ '{{ count }}' => $count ]);

        return $time;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->availableCacheDrivers = $container['cache/available-drivers'];
        $this->cache                 = $container['cache'];
        $this->cacheConfig           = $container['cache/config'];
    }
}
