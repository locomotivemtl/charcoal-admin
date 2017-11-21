<?php

namespace Charcoal\Admin\Ui\Sidemenu;

// From 'charcoal-ui'
use Charcoal\Ui\AbstractUiItem;

/**
 * Default implementation of {@see \Charcoal\Admin\Ui\Sidemenu\SidemenuGroupInterface}
 * as an abstract class.
 */
abstract class AbstractSidemenuGroup extends AbstractUiItem implements
    SidemenuGroupInterface
{
    use SidemenuGroupTrait;

    /**
     * Returns a new sidemenu group.
     *
     * @param array $data The class depdendencies.
     */
    public function __construct(array $data)
    {
        if (isset($data['sidemenu'])) {
            $this->setSidemenu($data['sidemenu']);
        }

        parent::__construct($data);
    }
}
