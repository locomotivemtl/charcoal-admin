<?php

namespace Charcoal\Admin\Property;

use \Exception as Exception;

use \Charcoal\Admin\Property\Input as Input;

// From `charcoal-core`
use \Charcoal\Core\AbstractFactory as AbstractFactory;
use \Charcoal\Property\PropertyInterface as PropertyInterface;

class InputFactory extends AbstractFactory
{
    /**
    * @param string $type
    * @throws Exception
    * @return ModelInterface
    */
    public function get($type)
    {
        $class_name = $this->_ident_to_classname($type);
        //var_dump($class_name);
        if (class_exists($class_name)) {
            $obj = new $class_name();
            if (!($obj instanceof Input)) {
                throw new Exception('Invalid property input: '.$type.' (not a property input)');
            }
            return $obj;
        } else {
            throw new Exception('Invalid property input: '.$type);
        }
    }

    /**
    * @param string @ident
    * @return string
    */
    protected function _ident_to_classname($ident)
    {
        $class = str_replace('/', '\\', $ident);
        $expl  = explode('\\', $class);

        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );

        $class = '\\'.ltrim( implode('\\', $expl), '\\' );
        return $class;
    }
}
