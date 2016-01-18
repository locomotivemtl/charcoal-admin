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
use \Charcoal\Property\PropertyInterface;

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
    public function defaultArguments()
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

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request); // Unused

        $climate = $this->climate();

        $climate->underline()->out(
            'Create a new object'
        );

        $objType = $this->argOrInput('obj-type');

        $model_factory = new ModelFactory();
        $obj = $model_factory->create($objType, [
            'logger'=>$this->logger
        ]);

        $properties = $obj->properties();

        $vals = [];
        foreach ($properties as $prop) {
            //$input = $climate->input(sprintf('Enter value for "%s":', $prop->label()));
            $input = $this->propertyToInput($prop);
            $vals[$prop->ident()] = $input->prompt();
        }

        $obj->setFlatData($vals);
        $ret = $obj->save();

        $climate->green()->out(
            "\n".sprintf('Success! Object "%s" created.', $ret)
        );

        return $response;
    }



}
