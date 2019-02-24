<?php

namespace Charcoal\Admin\Mustache;

// From Mustache
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Asset\StringAsset;
use Assetic\AssetManager;
use Mustache_LambdaHelper as LambdaHelper;

// From charcoal-view
use Charcoal\View\Mustache\HelpersInterface;

/**
 * Assets Helpers
 */
class AssetsHelpers implements HelpersInterface
{
    /**
     * @var AssetManager|mixed $assets The assetic assets manager.
     */
    private $assets;

    /**
     * @var string $action
     */
    private $action;

    /**
     * @var string $collection
     */
    private $collection;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['assets']) && $data['assets'] instanceof AssetManager) {
            $this->assets = $data['assets'];
        }
    }

    /**
     * Get the collection of helpers as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'assets' => $this
        ];
    }

    /**
     * Clear macros.
     *
     * @return void
     */
    protected function reset()
    {
        $this->action     = null;
        $this->collection = null;
        $this->ident      = null;
    }

    // Magic Methods
    // =========================================================================

    /**
     * Magic: Render the Mustache section.
     *
     * @param  string            $text   The translation key.
     * @param  LambdaHelper|null $helper For rendering strings in the current context.
     * @return string
     */
    public function __invoke($text = null, LambdaHelper $helper = null)
    {
        if ($helper) {
            $text = $helper->render($text);
        }
        $return = $this->{$this->action}($this->collection, $text);
        $text   = $return;

        $this->reset();

        if ($helper) {
            return $helper->render($text);
        }

        return $text;
    }

    /**
     * Magic: Determine if a property is set and is not NULL.
     *
     * Required by Mustache.
     *
     * @param  string $macro A domain, locale, or number.
     * @return boolean
     */
    public function __isset($macro)
    {
        return boolval($macro);
    }

    /**
     * Magic: Process domain, locale, and number.
     *
     * Required by Mustache.
     *
     * @param  string $macro A domain, locale, or number.
     * @return mixed
     */
    public function __get($macro)
    {
        if ($macro === 'assets') {
            return $this;
        }

        if (!$this->action) {
            $macro = '__'.$macro;
            if (!method_exists($this, $macro)) {
                return false;
            }
            $this->action = $macro;

            return $this;
        }

        if (!$this->collection) {
            $this->collection = $macro;

            return $this;
        }

        if (!$this->ident) {
            $this->ident = $macro;

            return $this;
        }

        return $this;
    }


    // Helpers Actions
    // ==========================================================================

    /**
     * @param string $collection The collection ident.
     * @param string $text       Asset string to inject.
     * @return string
     */
    protected function __inject($collection, $text)
    {
        if (!$this->assets->has($collection)) {
            $this->assets->set($collection, new AssetCollection());
        }

        $this->assets->get($collection)->add(
            new StringAsset($text)
        );

        return null;
    }

    /**
     * @param string $collection The collection ident.
     * @return void
     */
    protected function __enqueue($collection)
    {
        foreach ($this->assets->get($this->ident) as $asset) {
            $this->assets->get($collection)->add($asset);
        }
    }

    /**
     * @param string $collection The collection ident.
     * @return string
     */
    protected function __output($collection)
    {
        return $this->assets->get($collection)->dump();
    }
}
