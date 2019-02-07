<?php

namespace Charcoal\Admin\Service;

// from kriswallsmith/assetic
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Asset\FileAsset;
use Assetic\AssetManager;

// from charcoal-admin
use Charcoal\Admin\AssetsConfig;

/**
 * Assets Builder
 *
 * Build custom assets builder using {@link https://github.com/kriswallsmith/assetic}
 */
final class AssetsBuilder
{
    /**
     * @var AssetManager $am
     */
    private $am;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     * @param array $data The init options.
     * @return void
     * @link   http://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct(array $data = [])
    {
    }

    /**
     * The __invoke method is called when a script tries to call an object as a function.
     *
     * @param AssetsConfig $config The assets management config.
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
     */
    public function __invoke($config)
    {
        return $this->build($config);
    }

    /**
     * @param AssetsConfig $config The assets management config.
     * @return AssetManager
     */
    public function build($config)
    {
        $this->am = new AssetManager();
        $this->parseCollections($config->collections());

        return $this->am;
    }

    /**
     * @param array $collections Assets collections.
     * @return AssetManager
     */
    private function parseCollections(array $collections)
    {
        foreach ($collections as $collectionIdent => $actions) {
            $collection = [];

            if (isset($actions['dependencies'])) {
                foreach ($actions['dependencies'] as $d) {
                    $collection[] = new AssetReference($this->am, $d);
                }
            }

            if (isset($actions['files'])) {
                foreach ($actions['files'] as $f) {
                    $collection[] = new FileAsset(dirname(__DIR__, 7).'/'.$f, []);
                }
            }

            $ac = new AssetCollection($collection);
            $this->am->set($collectionIdent, $ac);
        }
    }
}
