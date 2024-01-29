<?php

namespace Charcoal\Admin\Service;

// from kriswallsmith/assetic
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
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
     * @return void
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
    public function __invoke(AssetsConfig $config)
    {
        return $this->build($config);
    }

    /**
     * @param AssetsConfig $config The assets management config.
     * @return AssetManager
     */
    public function build(AssetsConfig $config)
    {
        $this->am = new AssetManager();
        $this->parseCollections($config->collections());

        return $this->am;
    }

    /**
     * @param array $collections Assets collections.
     * @return void
     */
    private function parseCollections(array $collections)
    {
        foreach ($collections as $collectionIdent => $actions) {
            $files = ($actions['files'] ?? []);
            // Parse scoped files. Solves merging issues.
            array_walk($actions, function ($scope) use (&$files) {
                if (isset($scope['files']) && !empty($scope['files'])) {
                    $files = array_merge($files, $scope['files']);
                }
            });

            $files = array_unique($files);
            $collection = $this->extractFiles($files);

            $ac = new AssetCollection($collection);
            $this->am->set($collectionIdent, $ac);
        }
    }

    /**
     * @param array $files Files to convert to Collection assets.
     * @return array
     */
    private function extractFiles(array $files = [])
    {
        $basePath = dirname(__DIR__, 7).'/';
        $collection = [];

        foreach ($files as $f) {
            // Files with asterisks should be treated as glob.
            if (strpos($f, '*') !== false) {
                $collection[] = new GlobAsset($basePath.$f);
                continue;
            }

            // Files starting with '@' should be treated as assets reference.
            if ($f[0] === '@') {
                $f = ltrim($f, '@');

                $collection[] = new AssetReference($this->am, $f);
                continue;
            }

            $collection[] = new FileAsset($basePath.$f);
        }

        return $collection;
    }
}
