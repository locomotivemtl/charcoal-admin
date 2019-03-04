<?php

namespace Charcoal\Admin\Action\System\StaticWebsite;

use Exception;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

use GuzzleHttp\Client as GuzzleClient;

use Charcoal\Admin\AdminAction;

/**
 * Class RegenerateAction
 */
class UpdateAction extends AdminAction
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        if (!$this->baseUrl()) {
            $this->setSuccess(false);
            return $response->withStatus(500);
        }
        $url = $request->getParam('url');
        $relativeUrl = str_replace($this->baseUrl(), '', $url);
        $url = $this->baseUrl().$relativeUrl;

        $outputDir = $this->basePath.'cache/static/'.$relativeUrl;

        $ret = $this->cacheUrl($url, $outputDir, $response);

        $this->setSuccess($ret);
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
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['base_path'];
    }

    /**
     * @param string            $url       URL to fetch and cache.
     * @param string            $outputDir Output directory.
     * @param ResponseInterface $response  PSR-7 response.
     * @return boolean
     */
    protected function cacheUrl($url, $outputDir, ResponseInterface $response)
    {
        unset($response);

        if (!file_exists($outputDir)) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not regenerate static page: did not exist on filesystem.');
            return false;
        }

        // Previous static version must be deleted in order to generate a new one.
        if (file_exists($outputDir.'/index.php')) {
            unlink($outputDir.'/index.php');
        }
        if (file_exists($outputDir.'/index.html')) {
            unlink($outputDir.'/index.html');
        }

        try {
            $guzzleClient = new GuzzleClient();
            $static = $guzzleClient->request('GET', $url);
        } catch (Exception $e) {
            $this->setSuccess(false);
            return false;
        }
        if ($static->getStatusCode() !== 200) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not generate static page: response status not 200.');
            return false;
        }

        $headers = $static->getHeaders();
        if (!isset($headers['Content-Type'][0])) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not generate static page: content-type was not set on response.');
            return false;
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

        $body = $static->getBody();
        if (!$body) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not generate static page: body is empty.');
            return false;
        }

        $ret = file_put_contents($outputFile, $prefix.$body);
        return ($ret !== false);
    }
}
