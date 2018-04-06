<?php

namespace Charcoal\Admin\Action\System\StaticWebsite;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
class DeleteAllAction extends AdminAction
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
        $outputDir = $this->basePath.'cache/static/';
        if (!file_exists($outputDir)) {
            $this->setSuccess(false);
            $this->addFeedback('error', 'Can not delete all static pages: did not exist on filesystem.');
            return $response->withStatus(404);
        }
        $this->recursiveDelete($outputDir);

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
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['base_path'];
    }

    /**
     * @param string $dir Directory to delete.
     * @return void
     */
    private function recursiveDelete($dir)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $cmd = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $cmd($fileinfo->getRealPath());
        }
    }
}
