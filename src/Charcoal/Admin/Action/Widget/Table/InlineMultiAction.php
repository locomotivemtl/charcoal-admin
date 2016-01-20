<?php

namespace Charcoal\Admin\Action\Widget\Table;

// Dependencies from `PHP`
use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\ModelFactory as ModelFactory;

use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\Widget\ObjectForm as ObjectForm;
use \Charcoal\Admin\Widget\FormProperty as FormProperty;

/**
 *
 */
class InlineMultiAction extends AdminAction
{
    protected $objects;

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $objType = $request->getParam('obj_type');
        $objIds = $request->getParam('obj_ids');

        if (!$objType || !$objIds) {
            $this->setSuccess(false);
            return $response->withStatus(404);
        }

        try {
            $model_factory = new ModelFactory();
            $this->objects = [];
            foreach ($objIds as $objId) {
                $obj = $model_factory->create($objType);
                $obj->load($objId);
                if (!$obj->id()) {
                    continue;
                }

                $o = [];
                $o['id'] = $obj->id();

                $objForm = new ObjectForm([
                    'logger' => $this->logger()
                ]);
                $objForm->set_objType($objType);
                $objForm->set_objId($objId);
                $formProperties = $objForm->formProperties();
                foreach ($formProperties as $propertyIdent => $property) {
                    if (!($property instanceof FormProperty)) {
                        continue;
                    }
                    $p = $obj->p($propertyIdent);
                    $property->setProperty_val($p->val());
                    $property->setProp($p);
                    $inputType = $property->inputType();
                    $o['inlineProperties'][$propertyIdent] = $property->renderTemplate($inputType);
                }
                $this->objects[] = $o;
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
            'success' => $this->success(),
            'objects' => $this->objects,
            'feedbacks' => $this->feedbacks()
        ];
        return $results;
    }
}
