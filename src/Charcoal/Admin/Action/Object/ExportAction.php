<?php

namespace Charcoal\Admin\Action\Object;

// Dependencies from `PHP`
use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;

use \Pimple\Container;

use \Charcoal\Translation\TranslationString;
use \Charcoal\Admin\Service\Exporter;

/**
 * From abstractAction
 * - TranslationAware
 * - ModelAware
 */
class ExportAction extends AdminAction
{
    /**
     * Application configurations
     * @var $appConfig
     */
    private $appConfig;

    /**
     * PropertyFactory
     * The property factory used to output the displayVal
     * @var PropertyFactory $propertyFactory
     */
    private $propertyFactory;

    /**
     * Set dependencies.
     * @param Container $container Dependencies.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->appConfig = $container['config'];
        $this->propertyFactory = $container['property/factory'];
        parent::setDependencies($container);
    }


    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $params = $request->getParams();
        if (!isset($params['obj_type'])) {
            $this->setSuccess(false);
            return $response;
        }

        $this->setMode('csv');

        $exporter = new Exporter([
            'logger' => $this->logger,
            'config' => $this->appConfig,
            'factory' => $this->modelFactory(),
            'obj_type' => $params['obj_type'],
            'propertyFactory' => $this->propertyFactory
        ]);

        if (isset($params['ident'])) {
            $exporter->setExportIdent($params['ident']);
        }

        $exporter->process();

        // Kind of always true unless there are no keywords defined.
        $this->setSuccess(true);

        return $response;
    }

    /**
     * @return array
     */
    public function results()
    {
        $ret = '';
        return $ret;
    }
}
