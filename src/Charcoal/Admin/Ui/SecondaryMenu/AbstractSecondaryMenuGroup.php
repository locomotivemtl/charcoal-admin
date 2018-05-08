<?php

namespace Charcoal\Admin\Ui\SecondaryMenu;

// From 'charcoal-ui'
use Charcoal\Ui\AbstractUiItem;

/**
 * Default implementation of {@see \Charcoal\Admin\Ui\SecondaryMenu\SecondaryMenuGroupInterface}
 * as an abstract class.
 */
abstract class AbstractSecondaryMenuGroup extends AbstractUiItem implements
    SecondaryMenuGroupInterface
{
    use SecondaryMenuGroupTrait;

    /**
     * Returns a new secondary menu group.
     *
     * @param array $data The class depdendencies.
     */
    public function __construct(array $data)
    {
        if (isset($data['secondary_menu'])) {
            $this->setSecondaryMenu($data['secondary_menu']);
        }

        parent::__construct($data);
    }
}
