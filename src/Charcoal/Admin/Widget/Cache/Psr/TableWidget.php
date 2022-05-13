<?php

declare(strict_types=1);

namespace Charcoal\Admin\Widget\Cache\Psr;

use Charcoal\Admin\Support\Formatter;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Table Items Cache Widget
 */
class TableWidget extends AbstractPsrCacheWidget
{
    /**
     * @var array[]
     */
    protected $cacheItems;

    /**
     * @var \Charcoal\Translator\Translation|null
     */
    protected $description;

    /**
     * Retrieves the collection of cache pool items.
     *
     * @return array[]
     */
    public function getCacheItems(): array
    {
        if ($this->cacheItems === null) {
            $this->cacheItems = iterator_to_array($this->fetchCacheItems());
        }

        return $this->cacheItems;
    }

    /**
     * Counts the number of cache pool items.
     *
     * @return int
     */
    public function countCacheItems(): int
    {
        $items = $this->getCacheItems();

        if (is_countable($items)) {
            return count($items);
        }

        return iterator_count($items);
    }

    /**
     * Determines if there are any cache pool items.
     *
     * @return bool
     */
    public function hasAnyCacheItems(): bool
    {
        return (bool)$this->countCacheItems();
    }

    /**
     * @param  mixed $description The text widget description (main content).
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $this->translator()->translation($description);

        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|null
     */
    public function getDescription()
    {
        return $this->renderTemplate((string)$this->description);
    }

    /**
     * Retrieves the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        $data = parent::widgetDataForJs();

        return array_merge($data, [
            'label'       => (string)$this->label(),
            'description' => '{{=<% %>=}}'.(string)$this->description.'<%={{ }}=%>',
        ]);
    }

    /**
     * Fetches the collection of cache pool items.
     *
     * @return iterable
     */
    public function fetchCacheItems(): iterable
    {
        $keys = $this->getCacheItemKeys();

        $items = $this->getCacheInfo()->getCacheItems($keys);

        foreach ($items as $item) {
            yield $this->formatCacheItem($item);
        }
    }

    /**
     * Formats the cache item information.
     *
     * @param  array<string, mixed> $item The cache item.
     * @return array<string, mixed>
     */
    protected function formatCacheItem(array $item): array
    {
        $output = [
            'key'       => $item['formattedKey'],
            'locked'    => $item['locked'],
            'num_hits'  => number_format($item['hits']),
            'size'      => Formatter::formatBytes($item['size']),
            '_size'     => $item['size'],
            'created'   => null,
            '_created'  => null,
            'modified'  => null,
            '_modified' => null,
            'accessed'  => null,
            '_accessed' => null,
            'expires'   => null,
            '_expires'  => null,
        ];

        $format = 'Y-m-d H:i:s';

        if (isset($item['creationTime'])) {
            $creation = new DateTimeImmutable('@'.$item['creationTime']);

            $output['created'] = $output['_created'] = $creation->format($format);
        }

        if (isset($item['modifiedTime'])) {
            $modified = new DateTimeImmutable('@'.$item['modifiedTime']);

            $output['modified'] = $output['_modified'] = $modified->format($format);
        }

        if (isset($item['accessTime'])) {
            $accessed = new DateTimeImmutable('@'.$item['accessTime']);

            $output['accessed'] = $output['_accessed'] = $accessed->format($format);
        }

        if (isset($item['expirationTime'])) {
            $expiration = new DateTimeImmutable('@'.$item['expirationTime']);

            $output['_expires'] = $modified->format($format);

            if (isset($item['creationTime'])) {
                $output['expires'] = $this->formatTimeDiff($creation, $expiration);
            }
        }

        return $output;
    }

    /**
     * Human-readable time difference.
     *
     * Note: Adapted from CakePHP\Chronos.
     *
     * @see https://github.com/cakephp/chronos/blob/1.1.4/LICENSE
     *
     * @param  DateTimeInterface      $date1 The datetime to start with.
     * @param  DateTimeInterface|null $date2 The datetime to compare against.
     * @return string
     */
    protected function formatTimeDiff(
        DateTimeInterface $date1,
        DateTimeInterface $date2 = null
    ): string {
        $isNow = ($date2 === null);
        if ($isNow) {
            $date2 = new DateTimeImmutable('now', $date1->getTimezone());
        }
        $interval = $date1->diff($date2);

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

        $time = $this->translator()->transChoice($unit, $count, [
            '{{ count }}' => $count,
        ]);

        return $time;
    }
}
