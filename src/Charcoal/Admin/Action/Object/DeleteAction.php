<?php

namespace Charcoal\Admin\Action\Object;

use \Exception;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From `charcoal-core`
use \Charcoal\Model\ModelFactory;

use \Charcoal\Admin\AdminAction;

/**
* Admin Object Delete Action: Delete an object
*
* ## Parameters
* **Required parameters**
* - `username`
* - `password`
* **Optional parameters**
* - `next_url`
*
* ## Response
* - `success` true if login was successful, false otherwise.
*   - Failure should also send a different HTTP code: see below.
* - `feedbacks` (Optional) operation feedbacks, if any.
* - `next_url` Redirect URL, in case of successfull login.
*   - This is the `next_url` parameter if it was set, or the default admin URL if not
*
* ## HTTP Codes
* - `200` in case of a successful object deletion
* - `404` if any error occurs
*
* Ident: `charcoal/admin/action/object/delete`
*
* @see \Charcoal\Charcoal::app() The `Slim` application inside the core Charcoal object, used to read request and set response.
*/
class DeleteAction extends AdminAction
{

    public function set_data(array $data)
    {
        unset($data);
        return $this;
    }

    /**
    * @return void
    */
    public function run(ServerRequestInterface $request, ResponseInterface $response)
    {

        $obj_type = $request->getParam('obj_type');
        $obj_id = $request->getParam('obj_id');

        if (!$obj_type) {
            $this->set_success(false);
            return $this->output($response->withStatus(404));
        }

        if (!$obj_id) {
            $this->set_success(false);
            return $this->output($response->withStatus(404));
        }

        try {
            $obj = ModelFactory::instance()->get($obj_type);
            $obj->load($obj_id);
            if (!$obj->id()) {
                $this->set_success(false);
                return $this->output($response->withStatus(404));
            }
            $res = $obj->delete();
            if ($res) {
                $this->log_object_delete();
                $this->set_success(true);
                return $this->output($response);
            }
        } catch (Exception $e) {
            $this->set_success(false);
            return $this->output($response->withStatus(404));
        }

    }

    public function response()
    {
        $success = $this->success();

        $response = [
            'success'=>$success
        ];

        return $response;
    }

    public function log_object_delete()
    {
        // @todo
    }
}
