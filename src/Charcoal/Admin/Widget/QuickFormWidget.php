<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;
use \Exception;

use \Pimple\Container;

use \Charcoal\Admin\Widget\ObjectFormWidget;
use \Charcoal\Admin\Ui\ObjectContainerInterface;
use \Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Quick form
 * Created to have a quick form to edit on the go.
 * Developped at first for the attachment concept,
 * should be usable at all time calling the widget
 * load action.
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @author Bene Roch <ben@locomotive.ca>
 */
class QuickFormWidget extends ObjectFormWidget
{

    /**
     * @param array|ArrayInterface $data The widget data.
     * @return ObjectForm Chainable
     */
    public function setData($data)
    {
        $data = array_merge($_GET, $data);
        $data = array_merge($_POST, $data);
        parent::setData($data);
        return $this;
    }
}
