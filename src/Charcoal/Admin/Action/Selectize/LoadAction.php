<?php

namespace Charcoal\Admin\Action\Selectize;

use Exception;

// From Pimple
use Pimple\Container;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\LoadAction as BaseLoadAction;
use Charcoal\Admin\Action\Selectize\SelectizeRendererAwareTrait;

/**
 * Selectize Load Action
 */
class LoadAction extends BaseLoadAction
{
    use SelectizeRendererAwareTrait;

    /**
     * The collection to return.
     *
     * @var array|mixed
     */
    private $selectizeCollection;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'selectize_obj_type', 'selectize_prop_ident', 'selectize_property'
        ], parent::validDataFromRequest());
    }

    /**
     * @param  RequestInterface  $request  The request options.
     * @param  ResponseInterface $response The response to return.
     * @return ResponseInterface
     * @throws UnexpectedValueException If "obj_id" is passed as $request option.
     * @todo   Implement obj_id support for load object action
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $failMessage = $this->translator()->translation('Failed to load object(s)');
        $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
            '{{ errorMessage }}' => $failMessage
        ]);
        $reqMessage  = $this->translator()->translation(
            '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
        );

        $objType = $this->selectizeObjType();

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

        try {
            $selectizeInput = $this->selectizeInput();
            $choices = $selectizeInput->p()->choices();
            $this->setSelectizeCollection($this->selectizeVal($choices));

            $count = count($choices);
            switch ($count) {
                case 0:
                    $doneMessage = $this->translator()->translation('No objects found.');
                    break;

                case 1:
                    $doneMessage = $this->translator()->translation('One object found.');
                    break;

                default:
                    $doneMessage = strtr($this->translator()->translation('{{ count }} objects found.'), [
                        '{{ count }}' => $count
                    ]);
                    break;
            }
            $this->addFeedback('success', $doneMessage);
            $this->setSuccess(true);

            return $response;
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }

    /**
     * @return array|mixed
     */
    public function selectizeCollection()
    {
        return $this->selectizeCollection;
    }

    /**
     * @param array|mixed $selectizeCollection The collection to return.
     * @return self
     */
    public function setSelectizeCollection($selectizeCollection)
    {
        $this->selectizeCollection = $selectizeCollection;

        return $this;
    }

    /**
     * @return array
     */
    public function results()
    {
        return [
            'success'    => $this->success(),
            'feedbacks'  => $this->feedbacks(),
            'selectize'  => $this->selectizeCollection()
        ];
    }

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setSelectizeRenderer($container['selectize/renderer']);
        $this->setPropertyInputFactory($container['property/input/factory']);
        $this->setPropertyFactory($container['property/factory']);
    }
}
