<?php

namespace Charcoal\Admin\Action;

// Dependencies from `PHP`
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use \Pimple\Container;

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
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        $opts = [
            // 'debug' => true,
            'roots' => [
                [
                    'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path'          => 'uploads/',                 // path to files (REQUIRED)
                    'URL'           => dirname($_SERVER['PHP_SELF']) . '/uploads', // URL to files (REQUIRED)
                    'uploadDeny'    => ['all'],                // All Mimetypes not allowed to upload
                    'uploadAllow'   => ['image', 'text/plain'],// Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder'   => ['deny', 'allow'],      // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
                ]
            ]
        ];

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();

        return $response;
    }
}
