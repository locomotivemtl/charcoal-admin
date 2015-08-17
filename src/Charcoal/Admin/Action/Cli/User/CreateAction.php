<?php

namespace Charcoal\Admin\Action\Cli\User;

use \Exception as Exception;

use \Charcoal\Admin\Action\CliAction as CliAction;
use \Charcoal\Admin\User as User;

class CreateAction extends CliAction
{
    public function run()
    {
        $climate = $this->climate();

        $climate->underline()->out('Create a new Charcoal Administrator');

        if ($climate->arguments->defined('help')) {
            $climate->usage();
            die();
        }

        $climate->arguments->parse();
        $verbose = !$climate->arguments->get('quiet');

        $user = new User();
        try {
            $properties = $user->properties();

            $shown_props = [
                'username',
                'email',
                'password',
                //'groups',
                //'permissions'
            ];

            $vals = [];
            foreach ($properties as $prop) {
                if (!in_array($prop->ident(), $shown_props)) {
                    continue;
                }
                //$climate->dump($prop->type());
                if ($prop->type() == 'password') {
                     $input = $climate->password(sprintf('Enter value for "%s":', $prop->label()));
                } else {
                    $input = $climate->input(sprintf('Enter value for "%s":', $prop->label()));
                }
                $v = $input->prompt();

                $prop->set_val($v);
                $valid = $prop->validate();
                $vals[$prop->ident()] = $v;
            }

            $user->reset_password($vals['password']);
            unset($vals['password']);

            $user->set_flat_data($vals);

            $ret = $user->save();
            if($ret) {
                $climate->green()->out("\n".sprintf('Success! User "%s" created.', $ret));
            }
            else {
                //$climate->dump($user->validator()->error_results());
                $climate->red()->out("\nError. Object could not be created.");
            }
        } catch (Exception $e) {
            $climate->error($e->getMessage());
            die();
        }
    }

    /**
    * @return boolean
    */
    public function auth_required()
    {
        $proto = new User();
        $source = $proto->source();
        if ($source->table_exists() !== true) {
            return false;
        }
        if ($source->table_is_empty() === true) {
            return false;
        }
        return true;
    }

    public function response()
    {
        return [
            'success'=>$this->success()
        ];
    }
}
