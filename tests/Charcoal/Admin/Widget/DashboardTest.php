<?php

namespace Charcoal\Admin\Tests\Widget;

use \Charcoal\Admin\Widget\Dashboard as Dashboard;

class DashboardTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new Dashboard();
        $this->assertInstanceOf('\Charcoal\Admin\Widget\Dashboard', $obj);
    }

    public function testSetData()
    {
        $obj = new Dashboard();
        $ret = $obj->set_data([
            'layout'=>[
                'structure'=>[
                    'num_cols'=>2,
                    'cols'=>[1,1]
                ]
            ],
            /*'widgets'=>[
                'foo'=> [
                    'type'=>'charcoal/admin/widget/text'
                ]
            ]*/
        ]);
        $this->assertSame($ret, $obj);
        // @todo Test data is set

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_data(false);
    }
}
