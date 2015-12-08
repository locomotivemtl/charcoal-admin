<?php

namespace Charcoal\Admin\Script\Object;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Module `charcoal-core` dependencies
use \Charcoal\Model\ModelFactory;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminScript;

/**
*
*/
class CreateScript extends AdminScript
{

    /**
    * @return array
    */
    public function default_arguments()
    {
        $arguments = [
            'obj-type' => [
                'longPrefix'   => 'obj-type',
                'description'  => 'Object type',
                'defaultValue' => ''
            ],
            'obj-id' => [
                'longPrefiex'  => 'obj-id',
                'description'  => 'Object ID',
                'defaultValue' => ''
            ]
        ];

        $arguments = array_merge(parent::default_arguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $climate = $this->climate();

        $climate->underline()->out(
            'Create a new object'
        );

        $obj_type = $this->arg_or_input('obj-type');

        $model_factory = new ModelFactory();
        $obj = $model_factory->create($obj_type, [
            'logger'=>$this->logger()
        ]);

        $properties = $obj->properties();

        $vals = [];
        foreach ($properties as $prop) {
            //$input = $climate->input(sprintf('Enter value for "%s":', $prop->label()));
            $input = $this->property_to_input($prop);
            $vals[$prop->ident()] = $input->prompt();
        }

        $obj->set_flat_data($vals);
        $ret = $obj->save();

        $climate->green()->out(
            "\n".sprintf('Success! Object "%s" created.', $ret)
        );

    }

    public function property_to_input($prop)
    {
        $climate = $this->climate();

        if ($prop->type() == 'password') {
            $input = $climate->password(
                sprintf('Enter value for "%s":', $prop->label())
            );
        } else if ($prop->type() == 'boolean') {
            $opts = [
                1 => $prop->true_label(),
                0 => $prop->false_label()
            ];
            $input = $climate->radio(
                sprintf('Enter value for "%s":', $prop->label()),
                $opts
            );
        } else {
            $input = $climate->input(
                sprintf('Enter value for "%s":', $prop->label())
            );
            if ($prop->type() == 'text' || $prop->type == 'html') {
                $input->multiLine();
            }
        }
        return $input;
    }

}
