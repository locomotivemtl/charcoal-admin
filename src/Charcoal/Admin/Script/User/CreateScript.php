<?php

namespace Charcoal\Admin\Script\User;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use \Charcoal\Admin\AdminScript;

/**
 * Create user script.
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
        unset($request);

        $climate = $this->climate();

        $climate->underline()->out(
            'Create a new Charcoal Administrator'
        );

        $user = $this->modelFactory()->create('charcoal/admin/user');
        $properties = $user->properties();

        $shown_props = [
            'username',
            'email',
            'roles',
            'password'
        ];

        $vals = [];
        foreach ($properties as $prop) {
            if (!in_array($prop->ident(), $shown_props)) {
                continue;
            }
            if ($prop->type() == 'password') {
                 $input = $climate->password(sprintf('Enter value for "%s":', $prop->label()));
            } else {
                $input = $climate->input(sprintf('Enter value for "%s":', $prop->label()));
            }
            $input = $this->propertyToInput($prop);
            $v = $input->prompt();

            $prop->setVal($v);
            $valid = $prop->validate();
            $vals[$prop->ident()] = $v;
        }

        $user->resetPassword($vals['password']);
        unset($vals['password']);

        $user->setFlatData($vals);

        $ret = $user->save();
        if ($ret) {
            $climate->green()->out("\n".sprintf('Success! User "%s" created.', $ret));
        } else {
            $climate->red()->out("\nError. Object could not be created.");
        }

        return $response;
    }

    /**
     * @return boolean
     */
    public function authRequired()
    {
        return false;
    }
}
