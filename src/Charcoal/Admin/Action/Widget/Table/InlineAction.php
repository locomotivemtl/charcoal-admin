<?php

namespace Charcoal\Admin\Action\Widget\Table;

use \Exception;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Charcoal;
use \Charcoal\Model\ModelFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminAction as AdminAction;
use \Charcoal\Admin\Widget\ObjectFormWidget as ObjectFormWidget;
use \Charcoal\Admin\Widget\FormPropertyWidget as FormPropertyWidget;

/**
* Inline action: Return the inline edit properties HTML from an object
*
* ## Required parameters
* - `obj_type`
* - `obj_id`
*/
class InlineAction extends AdminAction
{
    protected $_inline_properties;

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $obj_type = $request->getParam('obj_type');
        $obj_id = $request->getParam('obj_id');

        if (!$obj_type || !$obj_id) {
            $this->set_success(false);
            return $response->withStatus(404);
        }

        try {
            $model_factory = new ModelFactory();
            $obj = $model_factory->create($obj_type);
            $obj->load($obj_id);
            if (!$obj->id()) {
                $this->set_success(false);
                return $response->withStatus(404);
            }

            $obj_form = new ObjectFormWidget([
                'logger' => $this->logger()
            ]);
            $obj_form->set_obj_type($obj_type);
            $obj_form->set_obj_id($obj_id);
            $form_properties = $obj_form->form_properties();
            foreach ($form_properties as $property_ident => $property) {
                if (!($property instanceof FormPropertyWidget)) {
                    continue;
                }
                $p = $obj->p($property_ident);
                $property->set_property_val($p->val());
                $property->set_prop($p);
                $input_type = $property->input_type();
                $this->_inline_properties[$property_ident] = $property->render_template($input_type);
            }
            $this->set_success(true);
            return $response;

        } catch (Exception $e) {
            $this->set_success(false);
            return $response->withStatus(404);
        }
    }

    /**
    * @return array
    */
    public function results()
    {
        $response = [
            'success'           => $this->success(),
            'inline_properties' => $this->_inline_properties,
            'feedbacks'         => $this->feedbacks()
        ];
        return $results;
    }
}
