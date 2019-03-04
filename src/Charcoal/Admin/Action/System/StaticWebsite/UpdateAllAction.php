<?php

namespace Charcoal\Admin\Action\System\StaticWebsite;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

use GuzzleHttp\Client as GuzzleClient;

use Charcoal\Admin\Action\System\StaticWebsite\UpdateAction;

/**
 * Update all static website files currently in cache.
 */
class UpdateAllAction extends UpdateAction
{
    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $files = $this->globRecursive($this->basePath.'cache/static', 'index.*');
        foreach ($files as $file) {
            $relativeUrl = dirname(str_replace($this->basePath.'cache/static/', '', $file));
            $url = $this->baseUrl().$relativeUrl;
            $outputDir = $this->basePath.'cache/static/'.$relativeUrl;
            $this->cacheUrl($url, $outputDir, $response);
        }
        $this->setSuccess(true);
        return $response;
    }

    /**
     * @return array
     */
    public function results()
    {
        $ret = [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];

        return $ret;
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
