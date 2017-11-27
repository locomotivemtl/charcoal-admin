<?php

namespace Charcoal\Admin\Script\Tools\StaticWebsite;

use InvalidArgumentException;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Pimple\Container;

use GuzzleHttp\Client as GuzzleClient;
use Goutte\Client as GoutteClient;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminScript;

/**
 *
 */
class CrawlScript extends AdminScript
{
    /**
     * @var string
     */
    private $startUrl;

    /**
     * @var array
     */
    private $parsedStartUrl;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $outputDir;

    /**
     * @var integer
     */
    private $maxLevel;

    /**
     * @var string[]
     */
    private $processedUrls = [];

    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * @var GoutteClient
     */
    private $goutteClient;

    /**
     * @param array|null $data Script data, if any.
     */
    public function __construct($data = null)
    {
        parent::__construct($data);

        $this->guzzleClient = new GuzzleClient();
        $this->goutteClient = new GoutteClient();
        $this->goutteClient->setClient($this->guzzleClient);
    }

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
                'description' => 'Base URL to start crawling from.',
                'defaultValue' => $this->baseUrl()
            ],
            'output-dir' => [
                'longPrefix' => 'output-dir',
                'description' => 'Output path (relative) where the static files will be stored.',
                'defaultValue' => 'www/static/'
            ],
            'max-level' => [
                'longPrefix' => 'max-level',
                'description'   => 'Maximum recursive level.',
                'defaultValue'  => 2
            ]
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

        $climate = $this->climate();
        $climate->arguments->parse();

        $this->startUrl = rtrim($climate->arguments->get('url'), '/').'/';
        $this->parsedStartUrl = parse_url($this->startUrl);

        $this->outputDir = rtrim($this->basePath.$climate->arguments->get('output-dir'), '/');

        $this->maxLevel = $climate->arguments->get('max-level');

        $climate->underline()->out(
            sprintf('Generate Static Website ("%s") with crawler', $this->startUrl)
        );

        $this->cacheUrl($this->startUrl);
        $this->retrieveLinks($this->startUrl, 0);

        return $response;
    }

    /**
     * @param string $url The URL to cache. The base (start) URL will be prefixed to relative URLs.
     * @return void
     */
    private function cacheUrl($url)
    {
        if (in_array($url, $this->processedUrls)) {
            return;
        }

        $relativeUrl = str_replace($this->startUrl, '', $url);
        $url = $this->startUrl.$relativeUrl;
        $outputDir = $this->outputDir.'/'.$relativeUrl;

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
        $this->processedUrls[] = $url;
    }

    /**
     * @param string  $url   The URL to retrieve links from.
     * @param integer $level Current level.
     * @return void
     */
    private function retrieveLinks($url, $level)
    {
        $crawler = $this->goutteClient->request('GET', $url);
        $crawler->filter('a')->each(function($item) use ($level) {
            $href = $item->attr('href');
            $parsedHref = parse_url($href);
            if (isset($parsedHref['host']) && ($parsedHref['host'] !== $this->parsedStartUrl['host'])) {
                return;
            }
            if (isset($parsedHref['scheme']) && $parsedHref['scheme'] == 'mailto') {
                return;
            }

            $nextUrl = str_replace($this->startUrl, '', $href);
            $this->cacheUrl($nextUrl);
            if ($level < $this->maxLevel) {
                $this->retrieveLinks($nextUrl, ++$level);
            }
        });
    }

    /**
     * @param string $dir The directory to recursively delete.
     * @throws InvalidArgumentException If the argument is empty.
     * @return mixed
     */
    private function recursiveDelete($dir)
    {
        if (!is_string($dir)) {
            throw new InvalidArgumentException(
                'Directory must be a string'
            );
        }
        if ($dir === '' || realdir($dir) === realdir($this->basePath)) {
            throw new InvalidArgumentException(
                'Static'
            );
        }
        $this->climate()->out('Deleting '.$dir);
        return;

        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            $f = $dir.'/'.$file;
            if (is_dir($f)) {
                $this->recursiveDelete($f);
            } else {
                unlink($f);
            }
        }
        return rmdir($dir);
    }
}
