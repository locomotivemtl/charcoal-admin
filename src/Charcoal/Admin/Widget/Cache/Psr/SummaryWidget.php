<?php

declare(strict_types=1);

namespace Charcoal\Admin\Widget\Cache\Psr;

use Charcoal\Admin\Support\Formatter;
use InvalidArgumentException;

/**
 * Summary Cache Widget
 */
class SummaryWidget extends AbstractPsrCacheWidget
{
    public const DEFAULT_SHOW_SUMMARY_MAP = [
        '*' => true,
    ];

    /**
     * @var array<string, mixed>
     */
    protected $cacheSummary;

    /**
     * Either a boolean flag or a map of summary field and boolean flag.
     *
     * @var array<string, bool>
     */
    protected $showSummaryMap;

    /**
     * @var \Charcoal\Translator\Translation|null
     */
    protected $description;

    /**
     * @var \Charcoal\Translator\Translation|string|null
     */
    protected $clearCacheButtonLabel;

    /**
     * Retrieves a summary of cache information.
     *
     * @return array<string, mixed>
     */
    public function getCacheSummary(): array
    {
        if ($this->cacheSummary === null) {
            $this->cacheSummary = $this->fetchCacheSummary();
        }

        return $this->cacheSummary;
    }

    /**
     * @return array<string, bool>
     */
    public function getDefaultShowSummaryMap(): array
    {
        return static::DEFAULT_SHOW_SUMMARY_MAP;
    }

    /**
     * @param  mixed $showSummary The show summary flag.
     * @throws InvalidArgumentException If the argument is invalid.
     * @return self
     */
    public function setShowSummary($showSummary)
    {
        if (is_bool($showSummary)) {
            $showSummary = [
                '*' => $showSummary,
            ];
        } elseif (!is_array($showSummary)) {
            throw new InvalidArgumentException(
                'Expected a boolean flag, or an associative array of fields, to show or hide'
            );
        }

        $this->setShowSummaryMap($showSummary);

        return $this;
    }

    /**
     * @param  array<string, bool> $showSummary The show summary flag map.
     * @throws InvalidArgumentException If the argument is invalid.
     * @return self
     */
    public function setShowSummaryMap(array $showSummary)
    {
        if (!isset($showSummary['*'])) {
            $counts = array_count_values(
                array_map(function ($flag) {
                    return $flag ? 'true' : 'false';
                }, $showSummary)
            );
            $showSummary['*'] = empty($counts['true']);
        }

        $this->showSummaryMap = $showSummary;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowSummary(): bool
    {
        return (bool) $this->getShowSummaryMap();
    }

    /**
     * @return array<string, bool>
     */
    public function getShowSummaryMap(): array
    {
        return $this->showSummaryMap ?? $this->getDefaultShowSummaryMap();
    }

    /**
     * @param  string|null $field The field to test.
     * @return bool
     */
    public function canShowSummaryField(string $field = null): bool
    {
        $summaryMap = $this->getShowSummaryMap();

        if ($field && $field !== '*' && isset($summaryMap[$field])) {
            return $summaryMap[$field];
        }

        return ($summaryMap['*'] ?? false);
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
     * @return string|null
     */
    public function getDescription()
    {
        return $this->renderTemplate((string)$this->description);
    }

    /**
     * Set the label for the clear cache button.
     *
     * @param  string|string[] $label The button label.
     * @return self
     */
    public function setClearCacheButtonLabel($label)
    {
        $this->clearCacheButtonLabel = $this->translator()->translation($label);

        return $this;
    }

    /**
     * Retrieve the label for the clear cache button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function getClearCacheButtonLabel()
    {
        if ($this->clearCacheButtonLabel === null) {
            $this->setClearCacheButtonLabel($this->getDefaultClearCacheButtonLabel());
        }

        return $this->clearCacheButtonLabel;
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
            'clear_cache_button_label' => (string)$this->getClearCacheButtonLabel(),
            'show_summary_map'         => $this->getShowSummaryMap(),
            'label'                    => (string)$this->label(),
            'description'              => '{{=<% %>=}}'.(string)$this->description.'<%={{ }}=%>',
        ]);
    }

    /**
     * Fetches a summary of cache information.
     *
     * @return array<string, mixed>
     */
    protected function fetchCacheSummary(): array
    {
        $info = $this->getCacheInfo();

        $keys = $this->getCacheItemKeys();

        $result = $info->getCacheSummary($keys);

        $adaptersArr = (array) $result['translatableName'];
        $adaptersStr = implode(', ', array_map(function ($adapter) {
            return ($this->translateCacheMessageKey($adapter) ?? $adapter);
        }, $adaptersArr));

        $translator = $this->translator();

        $summary = [];

        if ($this->canShowSummaryField('name')) {
            $summary['name'] = [
                'label' => $translator->trans('cache.info.name.label'),
                'value' => ($this->translateCacheMessageKey($info->getTranslatableName()) ?? $info->getName()),
                'raw'   => $info->getName(),
            ];
        }

        if ($this->canShowSummaryField('persistent')) {
            $summary['persistent'] = [
                'label' => $translator->trans('cache.info.persistent.label'),
                'value' => $translator->trans($result['isPersistent'] ? 'Yes' : 'No'),
                'raw'   => $result['isPersistent'],
            ];
        }

        if ($this->canShowSummaryField('adapter')) {
            $summary['adapter'] = [
                'label' => $translator->transChoice('cache.info.adapter.label', count($adaptersArr)),
                'value' => $adaptersStr,
                'raw'   => $adaptersArr,
            ];
        }

        if ($this->canShowSummaryField('count')) {
            $summary['count'] = [
                'label' => $translator->trans('cache.info.count.label'),
                'value' => ($result['totalCount'] !== null ? number_format($result['totalCount']) : $result['totalCount']),
                'raw'   => $result['totalCount'],
            ];
        }

        if ($this->canShowSummaryField('hits')) {
            $summary['hits'] = [
                'label' => $translator->trans('cache.info.hits.label'),
                'value' => ($result['totalHits'] !== null ? number_format($result['totalHits']) : $result['totalHits']),
                'raw'   => $result['totalHits'],
            ];
        }

        if ($this->canShowSummaryField('misses')) {
            $summary['misses'] = [
                'label' => $translator->trans('cache.info.misses.label'),
                'value' => ($result['totalMisses'] !== null ? number_format($result['totalMisses']) : $result['totalMisses']),
                'raw'   => $result['totalMisses'],
            ];
        }

        if ($this->canShowSummaryField('size')) {
            $summary['size'] = [
                'label' => $translator->trans('cache.info.size.label'),
                'value' => ($result['totalSize'] !== null ? Formatter::formatBytes($result['totalSize']) : $result['totalSize']),
                'raw'   => $result['totalSize'],
            ];
        }

        $summary['attributes'] = array_values(array_filter($summary, function ($attr) {
            return isset($attr['value']);
        }));

        $summary['notes'] = [];

        if ($this->canShowSummaryField('notes')) {
            if (!$result['isAvailable']) {
                $summary['notes'][] = $translator->trans('cache.info.available.notes');
            }

            foreach ($adaptersArr as $adapter) {
                $note = $this->translateCacheMessageKey(str_replace('.label', '.notes', $adapter));
                if ($note) {
                    $summary['notes'][] = $note;
                }
            }
        }

        return $summary;
    }

    /**
     * Retrieve the default label for the clear cache button.
     *
     * @return \Charcoal\Translator\Translation|null
     */
    protected function getDefaultClearCacheButtonLabel()
    {
        return $this->translator()->translation('Clear Cache');
    }

    /**
     * Attempts to localize the cache item pool name.
     *
     * @param  string $key The translatable cache item pool name.
     * @return ?string
     */
    protected function translateCacheMessageKey(string $key): ?string
    {
        $name = $this->translator()->trans($key);
        if ($key !== $name) {
            return $name;
        }

        return null;
    }
}
