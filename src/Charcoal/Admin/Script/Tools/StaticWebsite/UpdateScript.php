<?php

namespace Charcoal\Admin\Script\Tools\StaticWebsite;

use InvalidArgumentException;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Pimple\Container;

use GuzzleHttp\Client as GuzzleClient;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminScript;

/**
 * Update all static website files currently in cache.
 * Or optionally only update a single URL.
 */
class UpdateScript extends AdminScript
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;


    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['basePath'];
    }

    /**
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'url' => [
                'longPrefix' => 'url',
                'description' => 'Single URL to add.',
                'defaultValue' => null
            ],
            'all' => [
                'longPrefix' => 'all',
                'description' => 'Update all files currently in static website cache. Will override the --url option.',
                'noValue'      => true
            ],
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  PSR-7 Request.
     * @param ResponseInterface $response PSR-7 Response.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);
        $this->guzzleClient = new GuzzleClient();

        $climate = $this->climate();

        $climate->underline()->out(
            'Update Static Website'
        );

        $climate->arguments->parse();
        $url = $climate->arguments->get('url');
        $all = $climate->arguments->get('all');
        if (!$all && $url) {
            $files = [str_replace($this->baseUrl(), '', $url)];
        } else {
            $files = $this->globRecursive($this->basePath.'cache/static', 'index.*');
        }

        foreach ($files as $file) {
            $relativeUrl = dirname(str_replace($this->basePath.'cache/static/', '', $file));
            if ($relativeUrl === '.') {
                $relativeUrl = '';
            }
            $url = $this->baseUrl().$relativeUrl;
            $outputDir = $this->basePath.'cache/static/'.$relativeUrl;
            if ($this->verbose()) {
                $climate->out('Updating "'.$relativeUrl.'"...');
            }
            $this->cacheUrl($url, $outputDir);
        }

        return $response;
    }

    /**
     * @param string $url       The URL to cache. The base (start) URL will be prefixed to relative URLs.
     * @param string $outputDir The output directory.
     * @return void
     */
    private function cacheUrl($url, $outputDir)
    {
        $relativeUrl = str_replace($this->baseUrl(), '', $url);
        $url = $this->baseUrl().$relativeUrl;
        $outputDir = $outputDir.'/'.$relativeUrl;

        // Previous static version must be deleted in order to generate a new one.
        if (file_exists($outputDir.'/index.php')) {
            unlink($outputDir.'/index.php');
        }
        if (file_exists($outputDir.'/index.html')) {
            unlink($outputDir.'/index.html');
        }

        $response = $this->guzzleClient->request('GET', $url);

        $headers = $response->getHeaders();
        if (!isset($headers['Content-Type'][0])) {
            // No content type. Don't process.
            return;
        }

        if (strstr($headers['Content-Type'][0], 'text/html') !== false) {
            $outputFile = $outputDir.'/index.html';
            $prefix = '';
        } else {
            $outputFile = $outputDir.'/index.php';
            $prefix = '<?php
            header("Content-Type: '.$headers['Content-Type'][0].'");
            ?>
            ';
        }

        if (!file_exists($outputDir)) {
            mkdir($outputDir, null, true);
        }

        file_put_contents($outputFile, $prefix.$response->getBody());
    }

    /**
     * @param string  $dir     Initial directory.
     * @param string  $pattern File pattern.
     * @param integer $flags   Glob flags.
     * @return array
     */
    protected function globRecursive($dir, $pattern, $flags = 0)
    {
        $files = glob($dir.'/'.$pattern, $flags);
        foreach (glob($dir.'/*', (GLOB_ONLYDIR|GLOB_NOSORT)) as $dir) {
            $files = array_merge($files, $this->globRecursive($dir, $pattern, $flags));
        }
        return $files;
    }
}
