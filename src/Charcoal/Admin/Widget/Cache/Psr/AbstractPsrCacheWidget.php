<?php

declare(strict_types=1);

namespace Charcoal\Admin\Widget\Cache\Psr;

use Charcoal\Admin\AdminWidget;
use Charcoal\Cache\CacheConfig;
use Charcoal\Cache\CachePoolAwareTrait;
use Charcoal\Cache\Information\PoolInformationInterface;
use Charcoal\Cache\Information\PoolInformationFactory;
use InvalidArgumentException;
use Pimple\Container;

/**
 * Base Cache Widget
 */
abstract class AbstractPsrCacheWidget extends AdminWidget
{
    use CachePoolAwareTrait;

    /**
     * The cache item pool name to target.
     *
     * If NULL, the default cache pool is targeted.
     *
     * @var ?string
     */
    private $cacheItemPool;

    /**
     * The cache item keys to target.
     *
     * @var string[]
     */
    private $cacheItemKeys = [];

    /**
     * The cache information aggregator.
     *
     * @var PoolInformationInterface
     */
    private $cacheInfo;

    /**
     * @param  string $pool A cache item pool name.
     * @return self
     */
    public function setCacheItemPool(?string $pool): self
    {
        if ($pool !== null) {
            $this->getCacheInfo()->assertValidItemPool($pool);
        }

        $this->cacheItemPool = $pool;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getCacheItemPool(): ?string
    {
        return $this->cacheItemPool;
    }

    /**
     * @param  string $key A cache item key.
     * @return self
     */
    public function setCacheItemKey(string $key): self
    {
        $this->setCacheItemKeys([ $key ]);

        return $this;
    }

    /**
     * @param  string[] $keys Zero or more cache item keys.
     * @throws InvalidArgumentException If the argument is invalid.
     * @return self
     */
    public function setCacheItemKeys(array $keys): self
    {
        $keys = $this->getCacheInfo()->filterItemKeys($keys);

        $this->cacheItemKeys = $keys;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getCacheItemKeys(): array
    {
        return $this->cacheItemKeys;
    }

    /**
     * Retrieves the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        return [
            'cache_item_pool' => $this->getCacheItemPool(),
            'cache_item_keys' => $this->getCacheItemKeys(),
        ];
    }

    /**
     * Retrieves the cache information aggregator.
     *
     * @return PoolInformationInterface
     */
    protected function getCacheInfo(): PoolInformationInterface
    {
        if ($this->cacheInfo === null) {
            $this->cacheInfo = PoolInformationFactory::create($this->cachePool());
        }

        return $this->cacheInfo;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setCachePool($container['cache']);
    }
}
