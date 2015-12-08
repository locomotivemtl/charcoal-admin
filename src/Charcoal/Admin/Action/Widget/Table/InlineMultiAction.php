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
    protected $_objects;

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $obj_type = $request->getParam('obj_type');
        $obj_ids = $request->getParam('obj_ids');

        if (!$obj_type || !$obj_ids) {
            $this->set_success(false);
            return $response->withStatus(404);
        }

        try {
            $model_factory = new ModelFactory();
            $this->_objects = [];
            foreach ($obj_ids as $obj_id) {
                $obj = $model_factory->create($obj_type);
                $obj->load($obj_id);
                if (!$obj->id()) {
                    continue;
                }

                $o = [];
                $o['id'] = $obj->id();

                $obj_form = new ObjectForm([
                    'logger' => $this->logger()
                ]);
                $obj_form->set_obj_type($obj_type);
                $obj_form->set_obj_id($obj_id);
                $form_properties = $obj_form->form_properties();
                foreach ($form_properties as $property_ident => $property) {
                    if (!($property instanceof FormProperty)) {
                        continue;
                    }
                    $p = $obj->p($property_ident);
                    $property->set_property_val($p->val());
                    $property->set_prop($p);
                    $input_type = $property->input_type();
                    $o['inline_properties'][$property_ident] = $property->render_template($input_type);
                }
                $this->_objects[] = $o;
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
        $results = [
            'success' => $this->success(),
            'objects' => $this->_objects,
            'feedbacks' => $this->feedbacks()
        ];
        return $results;
    }
}
