<?php

namespace Charcoal\Admin\Action\Cli;

use \Charcoal\Action\CliAction as CliAction;

class Test extends CliAction
{
    public function run()
    {
        var_dump('test cli');
    }

    public function response()
    {
        return [
            'success'=>$this->success()
        ];
    }
}
