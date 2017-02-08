<?php

namespace Charcoal\Admin\Script\User\AclRole;

// PSR-7 (http messaging) dependencies
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Intra-module (`charcoal-admin`) dependencies
use Charcoal\Admin\AdminScript;

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
            'Create a new Acl Role'
        );

        $role = $this->modelFactory()->create('charcoal/admin/user/acl-role');
        $properties = $role->properties();

        $shown_props = [
            'ident',
            'parent',
            'allowed',
            'denied',
            'superuser'
        ];

        $vals = [];
        foreach ($properties as $prop) {
            if (!in_array($prop->ident(), $shown_props)) {
                continue;
            }
            $input = $this->propertyToInput($prop);
            $v = $input->prompt();

            $prop->setVal($v);
            $valid = $prop->validate();
            $vals[$prop->ident()] = $v;
        }

        $role->setFlatData($vals);

        $ret = $role->save();
        if ($ret) {
            $climate->green()->out("\n".sprintf('Success! Role "%s" created.', $ret));
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
