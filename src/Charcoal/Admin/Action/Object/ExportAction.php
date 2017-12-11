<?php

namespace Charcoal\Admin\Action\Object;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Service\Exporter;

/**
 * Action: Export one or more objects from storage.
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 *
 * ## Optional Parameters
 *
 * - `ident`
 *
 * ## Response
 *
 * The response is in CSV mode.
 *
 * - `success` (_boolean_) — TRUE if the object(s) was/were exported, FALSE in case of any error.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Object(s) expoerted, if any
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Object(s) could not be expoerted, if any
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
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $failMessage = $this->translator()->translation('Failed to export object(s)');
        $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
            '{{ errorMessage }}' => $failMessage
        ]);
        $reqMessage  = $this->translator()->translation(
            '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
        );
        $typeMessage = $this->translator()->translation(
            '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}'
        );

        $objType     = $request->getParam('obj_type');
        $exportIdent = $request->getParam('ident');

        if (!$objType) {
            $actualType = is_object($objType) ? get_class($objType) : gettype($objType);
            $this->addFeedback('error', strtr($reqMessage, [
                '{{ parameter }}'    => '"obj_type"',
                '{{ expectedType }}' => 'string',
                '{{ actualType }}'   => $actualType,
            ]));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        /** @todo Does this do anything? */
        $this->setMode('csv');

        $exporter = new Exporter([
            'logger'          => $this->logger,
            'factory'         => $this->modelFactory(),
            'obj_type'        => $objType,
            'propertyFactory' => $this->propertyFactory,
            'translator'      => $this->translator()
        ]);

        if (isset($exportIdent)) {
            if (!is_string($exportIdent)) {
                $actualType = is_object($exportIdent) ? get_class($exportIdent) : gettype($exportIdent);
                $this->addFeedback('error', strtr($typeMessage, [
                    '{{ parameter }}'    => 'Export "ident"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]));
                $this->setSuccess(false);

                return $response->withStatus(400);
            }

            $exporter->setExportIdent($exportIdent);
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
        return [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->propertyFactory = $container['property/factory'];
    }
}
