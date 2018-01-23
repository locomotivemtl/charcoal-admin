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
        $cachePath = $this->basePath.'cache';
        // Ensure 'cache' directory exists
        if (!file_exists($cachePath)) {
            $ret = mkdir($cachePath);
            if ($ret === false) {
                $this->setSuccess(false);
                return $response->withStatus(500);
            }
        }

        $staticPath = $cachePath.'/static';
        // Ensure 'cache/static' directory exists
        if (!file_exists($staticPath)) {
            $ret = mkdir($staticPath);
            if ($ret === false) {
                $this->setSuccess(false);
                return $response->withStatus(500);
            }
        }

        $publicPath = $this->basePath.'www';
        $staticLink = $publicPath.'/static';
        // Ensure 'www/static' directory does NOT exist
        if (file_exists($staticLink)) {
            $this->setSuccess(false);
            return $response->withStatus(409);
        }

        // Ensure 'www' directory exists
        if (!file_exists($publicPath)) {
            $ret = mkdir($publicPath);
            if ($ret === false) {
                $this->setSuccess(false);
                return $response->withStatus(500);
            }
        }

        // Create a symbolic link from 'www/static' to 'cache/static'
        $ret = symlink($staticPath, $staticLink);
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
