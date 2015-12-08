<?php

namespace Charcoal\Admin\Script\User;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminScript;
use \Charcoal\Admin\User;

/**
*
*/
class CreateScript extends AdminScript
{
    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $climate = $this->climate();

        $climate->underline()->out(
            'Create a new Charcoal Administrator'
        );

        $user = new User();
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
        if ($ret) {
            $climate->green()->out("\n".sprintf('Success! User "%s" created.', $ret));
        } else {
            //$climate->dump($user->validator()->error_results());
            $climate->red()->out("\nError. Object could not be created.");
        }

        return $response;
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
}
