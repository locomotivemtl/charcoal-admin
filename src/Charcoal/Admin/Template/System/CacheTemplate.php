<?php

declare(strict_types=1);

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

// From Charcoal
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Cache\CacheConfig;
use Charcoal\Cache\CachePoolAwareTrait;
use Charcoal\Cache\Information\PoolInformationInterface;
use Charcoal\Cache\Information\PoolInformationFactory;

/**
 * Cache status and information.
 */
class CacheTemplate extends AdminTemplate implements
    DashboardContainerInterface
{
    use CachePoolAwareTrait;
    use DashboardContainerTrait;

    /**
     * The cache information aggregator.
     *
     * @var PoolInformationInterface
     */
    private $cacheInfo;

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
     * Retrieve the secondary menu.
     *
     * @return \Charcoal\Admin\Widget\SecondaryMenuWidgetInterface|null
     */
    public function secondaryMenu()
    {
        if ($this->secondaryMenu === null) {
            $this->secondaryMenu = $this->createSecondaryMenu('system');
        }

        return $this->secondaryMenu;
    }

    /**
     * @return mixed
     */
    public function dashboardConfig()
    {
        if ($this->dashboardConfig === null) {
            $this->dashboardConfig = $this->getDefaultDashboardConfig();
        }

        return $this->dashboardConfig;
    }

    /**
     * Creates the default dashboard configset.
     *
     * @return array<string, mixed>
     */
    public function getDefaultDashboardConfig()
    {
        return [
            'layout' => [
                'structure' => [
                    [ 'columns' => [ 1 ] ],
                    [ 'columns' => [ 1, 1 ] ],
                    [ 'columns' => [ 1 ] ],
                ],
            ],
            'widgets' => [
                'charcoal/admin/cache/psr/pool/summary' => [
                    'active'   => true,
                    'priority' => 11,
                ],
                'charcoal/admin/cache/psr/pages/summary' => [
                    'active'   => true,
                    'priority' => 12,
                ],
                'charcoal/admin/cache/psr/objects/summary' => [
                    'active'   => true,
                    'priority' => 13,
                ],
                'charcoal/admin/cache/psr/pool/items' => [
                    'active'   => true,
                    'priority' => 14,
                ],
            ],
        ];
    }

    /**
     * Creates the default dashboard configset.
     *
     * @return array<string, mixed>
     */
    public function createDashboardConfig()
    {
        $widgets = $this->adminConfig('widgets');

        $dashboardConfig = array_replace_recursive(
            [
                'widgets' => $widgets,
            ],
            $this->dashboardConfig()
        );

        return $dashboardConfig;
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

        $this->setDashboardBuilder($container['dashboard/builder']);
    }
}
