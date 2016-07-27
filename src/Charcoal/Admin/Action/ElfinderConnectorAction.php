<?php

namespace Charcoal\Admin\Action;

use \InvalidArgumentException;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from Pimple
use \Pimple\Container;

// Dependencies from elFinder
use \elFinderConnector;
use \elFinder;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;

/**
 * Elfinder connector
 */
class ElfinderConnectorAction extends AdminAction
{
    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->appConfig = $container['config'];
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        $opts = [
            'debug' => false,
            'roots' => [
                [
                    // Driver for accessing file system (REQUIRED)
                    'driver'        => 'LocalFileSystem',
                    // Path to files (REQUIRED)
                    'path'          => 'uploads/',
                    // URL to files (REQUIRED)
                    'URL'           => $this->appConfig['URL'].'uploads',
                    // All MIME types not allowed to upload
                    'uploadDeny'    => [ 'all' ],
                    // MIME type `image` and `text/plain` allowed to upload
                    'uploadAllow'   => [ 'image', 'application/pdf', 'text/plain' ],
                    // Allowed MIME type `image` and `text/plain` only
                    'uploadOrder'   => [ 'deny', 'allow' ],
                    // Disable and hide dot starting files (OPTIONAL)
                    'accessControl' => 'access',
                    // File permission attributes
                    'attributes'    => [
                        [
                            // Block access to all hidden files and directories (anything starting with ".")
                            'pattern' => '!(?:^|/)\..+$!',
                            'read'    => false,
                            'write'   => false,
                            'hidden'  => true,
                            'locked'  => false
                        ]
                    ]
                ]
            ]
        ];

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();

        return $response;
    }
}
