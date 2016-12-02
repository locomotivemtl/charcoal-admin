<?php

namespace Charcoal\Admin\Action\Object;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction;

use \Pimple\Container;

use \Charcoal\Admin\Service\Exporter;

/**
 * From abstractAction
 * - TranslationAware
 * - ModelAware
 *
 * ## Parameters
 *
 * **Required parameters**
 *
 * - `obj_type`
 *
 * ** Optional parameters**
 *
 * - `ident`
 *
 * ## Response
 *
 * The response is in "csv" mode.
 */
class ExportAction extends AdminAction
{
    /**
     * Store the factory instance for the current class.
     *
     * @var \Charcoal\Factory\FactoryInterface
     */
    private $propertyFactory;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
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
            $this->addFeedback('error', 'Missing object type.');
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        // Does this do anything?
        $this->setMode('csv');

        $exporter = new Exporter([
            'logger'          => $this->logger,
            'factory'         => $this->modelFactory(),
            'obj_type'        => $params['obj_type'],
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
        $ret = [
            'success' => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];

        return $ret;
    }
}
