<?php

namespace Charcoal\Admin\Script\Tools;

use Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Pimple\Container;

use Goutte\Client as GoutteClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\TransferStats;

use Charcoal\Admin\AdminScript;

/**
 *
 */
class CheckLinksScript extends AdminScript
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
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'url' => [
                'longPrefix' => 'url',
                'description' => 'Object type',
                'defaultValue' => $this->baseUrl()
            ],
            'max-level' => [
                'longPrefix' => 'max-level',
                'description'   => 'Maximum recursive level.',
                'defaultValue'  => 2
            ],
            'verbose' => [
                'prefix'    => 'v',
                'longPrefix' => 'verbose',
                'description' => 'When verbose is disabled, only links returning errors will display output.',
                'defaultValue' => true
            ]
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  PSR-7 request.
     * @param ResponseInterface $response PSR-7 response.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $climate = $this->climate();
        $climate->arguments->parse();

        $this->startUrl = rtrim($climate->arguments->get('url'), '/').'/';
        $this->maxLevel = $climate->arguments->get('max-level');
        $this->setVerbose($climate->arguments->get('verbose'));

        $climate->underline()->out(
            sprintf('Check Broken Links ("%s")', $this->startUrl)
        );

        $this->checkUrl($this->startUrl);
        $this->retrieveLinks($this->startUrl, 0);

        return $response;
    }

    /**
     * @param string $url The URL to check.
     * @return void
     */
    private function checkUrl($url)
    {
        $rawUrl = $url;
        $this->processedUrls[] = $url;

        if ($this->validateLink($url) === false) {
            if ($this->verbose()) {
                $this->climate()->orange(sprintf('=> Url skipped (invalid): "%s"', $url));
            }
            return;
        }
        $url = $this->absoluteLink($url);

        try {
            $response = $this->guzzleClient->request('GET', $url, [
                'http_errors' => false,
                'on_stats' => function (TransferStats $stats) {
                    if ($stats->hasResponse()) {
                        $code = $stats->getResponse()->getStatusCode();
                        $transferTime = (1000*$stats->getTransferTime());
                        $effectiveUrl = (string)$stats->getEffectiveUri();
                        if ($this->verbose() && $code > 200 && $code < 400) {
                            $this->climate()->orange(sprintf(
                                '[%s] - %s - %sms',
                                $code,
                                $effectiveUrl,
                                number_format($transferTime, 0)
                            ));
                        } elseif ($code >= 400) {
                            $this->climate()->error(sprintf(
                                '[%s] - %s - %sms',
                                $code,
                                $effectiveUrl,
                                number_format($transferTime, 0)
                            ));
                        } elseif ($this->verbose()) {
                            $this->climate()->out(sprintf(
                                '[%s] - %s - %sms',
                                $code,
                                $effectiveUrl,
                                number_format($transferTime, 0)
                            ));
                        }
                    }
                }
            ]);
        } catch (Exception $e) {
            // Do nothing
            $this->climate()->error('-- Error retrieving '.$url);
        }
        if ($rawUrl !== $url) {
            $this->processedUrls[] = $rawUrl;
        }
    }

    /**
     * @param string  $url   The URL to retrieve links from.
     * @param integer $level The current level.
     * @return void
     */
    private function retrieveLinks($url, $level)
    {

        $crawler = $this->goutteClient->request('GET', $url);
        $crawler->filter('a')->each(function($item) use ($level) {
            $href = $item->attr('href');
            if (in_array($href, $this->processedUrls)) {
                return;
            }
            $this->checkUrl($href);
            if ($this->isInternalLink($href) && ($level < $this->maxLevel)) {
                $this->retrieveLinks($href, ++$level);
            }
        });
    }

    /**
     * @param string $url The URL to check.
     * @return boolean
     */
    private function isInternalLink($url)
    {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return true;
        }
        if ($parsed['host'] === $this->parsedStartUrl['host']) {
            return true;
        }
        return false;
    }

    /**
     * @param string $url The URL to convert to absolute.
     * @return string
     */
    private function absoluteLink($url)
    {
        if (strstr($url, 'http') === false) {
            return $this->startUrl.ltrim($url, '/');
        } else {
            return $url;
        }
    }

    /**
     * @param string $url The URL to valdate.
     * @return boolean
     */
    private function validateLink($url)
    {
        $parsed = parse_url($url);
        if (isset($parsed['scheme'])) {
            if (!in_array($parsed['scheme'], ['http', 'https'])) {
                return false;
            }
        }
        return true;
    }
}
