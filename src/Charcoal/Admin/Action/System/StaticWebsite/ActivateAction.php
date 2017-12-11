<?php

namespace Charcoal\Admin\Action\System\StaticWebsite;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

use Charcoal\Admin\AdminAction;

/**
 * Class ActivateAction
 */
class ActivateAction extends AdminAction
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $baseCache = $this->basePath.'cache/static';
        if (!file_exists($baseCache)) {
            $ret = mkdir($baseCache, null, true);
            if ($ret === false) {
                $this->setSuccess(false);
                return $response->withStatus(500);
            }
        }
        $staticLink = $this->basePath.'www/static';
        if (file_exists($staticLink)) {
            $this->setSuccess(false);
            return $response->withStatus(409);
        }
        if (!file_exists(dirname($staticLink))) {
            $ret = mkdir(dirname($staticLink));
            if ($ret === false) {
                $this->setSuccess(false);
                return $response->withStatus(500);
            }
        }
        $ret = symlink($baseCache, $staticLink);
        if ($ret === false) {
            $this->setSuccess(false);
            return $response->withStatus(500);
        } else {
            $this->setSuccess(true);
            return $response;
        }
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
}
