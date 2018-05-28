<?php

namespace Charcoal\Tests\Admin\Mock;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

// From 'charcoal-admin'
use Charcoal\Tests\AbstractTestCase;

/**
 * Mock Sortable Model
 */
class SortableModel extends AbstractModel
{
    /**
     * @param array $data Dependencies.
     */
    public function __construct(array $data = null)
    {
        $data['metadata'] = [
            'properties' => [
                'id' => [
                    'type' => 'id',
                    'mode' => 'custom'
                ],
                'position' => [
                    'type' => 'number'
                ]
            ],
            'sources' => [
                'default' => [
                    'table' => 'charcoal_admin_sortable_models'
                ]
            ],
            'default_source' => 'default'
        ];

        parent::__construct($data);
    }
}
