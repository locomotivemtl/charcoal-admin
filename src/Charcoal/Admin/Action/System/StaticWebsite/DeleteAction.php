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
class DeleteAction extends AdminAction
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

        $outputDir = $this->basePath.'cache/static/'.$relativeUrl;
        if (!file_exists($outputDir)) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not delete static page: did not exist on filesystem.');
            return $response->withStatus(404);
        }

        // Previous static version must be deleted in order to generate a new one.
        if (file_exists($outputDir.'/index.php')) {
            unlink($outputDir.'/index.php');
        }
        if (file_exists($outputDir.'/index.html')) {
            unlink($outputDir.'/index.html');
        }

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
