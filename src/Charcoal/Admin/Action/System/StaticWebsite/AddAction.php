<?php

namespace Charcoal\Admin\Action\System\StaticWebsite;

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
class AddAction extends AdminAction
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['base_path'];
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $url = $request->getParam('url');
        $relativeUrl = str_replace($this->baseUrl(), '', $url);
        $url = $this->baseUrl().$relativeUrl;

        $outputDir = $this->basePath.'cache/static/'.$relativeUrl;
        if (!file_exists($outputDir)) {
            mkdir($outputDir, null, true);
        }

        // Previous static version must be deleted in order to generate a new one.
        if (file_exists($outputDir.'/index.php')) {
            unlink($outputDir.'/index.php');
        }
        if (file_exists($outputDir.'/index.html')) {
            unlink($outputDir.'/index.html');
        }

        $guzzleClient = new GuzzleClient();
        $static = $guzzleClient->request('GET', $url, [
            'http_errors' => false
        ]);

        if ($static->getStatusCode() !== 200) {
            $this->setSuccess(false);
            $this->addFeedback('error', sprintf('Can not generate static page: response status was %s (needs a 200 OK response).', $static->getStatusCode()));
            return $response->withStatus(500);
        }

        $headers = $static->getHeaders();
        if (!isset($headers['Content-Type'][0])) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not generate static page: content-type was not set on response.');
            return $response->withStatus(500);
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
            return $response->withStatus(500);
        }

        $ret = file_put_contents($outputFile, $prefix.$body);

        $this->setSuccess(($ret !== false));
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
}
