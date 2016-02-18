<?php

namespace Charcoal\Admin\Action\Widget\Table;

use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Model\ModelFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\Widget\ObjectFormWidget as ObjectFormWidget;
use \Charcoal\Admin\Widget\FormPropertyWidget as FormPropertyWidget;

/**
 * Inline action: Return the inline edit properties HTML from an object
 *
 * ## Required parameters
 * - `objType`
 * - `objId`
 */
class InlineAction extends AdminAction
{
    /**
    * @var array $inlineProperties
    */
    protected $inlineProperties;

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $objType = $request->getParam('obj_type');
        $objId = $request->getParam('obj_id');

        if (!$objType || !$objId) {
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        try {
            $modelFactory = new ModelFactory();
            $obj = $modelFactory->create($objType, [
                'logger' => $this->logger
            ]);
            $obj->load($objId);
            if (!$obj->id()) {
                $this->setSuccess(false);
                return $response->withStatus(404);
            }

            $obj_form = new ObjectFormWidget([
                'logger' => $this->logger()
            ]);
            $obj_form->setObjType($objType);
            $obj_form->setObjId($objId);
            $formProperties = $obj_form->formProperties();
            foreach ($formProperties as $propertyIdent => $property) {
                if (!($property instanceof FormPropertyWidget)) {
                    continue;
                }
                $p = $obj->p($propertyIdent);
                $property->setPropertyVal($p->val());
                $property->setProp($p);
                $inputType = $property->inputType();
                $this->inlineProperties[$propertyIdent] = $property->renderTemplate($inputType);
            }
            $this->setSuccess(true);
            return $response;

        } catch (Exception $e) {
            $this->setSuccess(false);
            return $response->withStatus(404);
        }
    }

    /**
     * @return array
     */
    public function results()
    {
        $results = [
            'success'           => $this->success(),
            'inline_properties' => $this->inlineProperties,
            'feedbacks'         => $this->feedbacks()
        ];
        return $results;
    }
}
