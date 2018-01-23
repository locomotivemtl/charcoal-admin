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
 * Class PreviewAction
 */
class PreviewAction extends AdminAction
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $fileContent = '';

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
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not preview static page: does not exist on filesystem.');
            return $response->withStatus(404);
        }

        if (file_exists($outputDir.'/index.html')) {
            $this->fileContent = file_get_contents($outputDir.'/index.html');
            $this->setSuccess(true);
            return $response;
        }
        // Previous static version must be deleted in order to generate a new one.
        if (file_exists($outputDir.'/index.php')) {
            $this->fileContent = file_get_contents($outputDir.'/index.php');
            $this->setSuccess(true);
            return $response;
        }

        $this->setSuccess(false);
        return $response->withStatus(404);
    }

    /**
     * @return array
     */
    public function results()
    {
        $ret = [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks(),
            'content' => $this->fileContent
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
}
