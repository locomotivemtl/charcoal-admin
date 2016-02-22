<?php
namespace Charcoal\Admin\Widget\Graph;

use \Charcoal\Admin\Widget\GraphWidget;

/**
 *
 */
class BarWidget extends GraphWidget
{
    public function series()
    {
        return json_encode([
            [
                'name' => 'Serie 1',
                'type' => 'bar',
                'data' => [12.0, 14.9, 7.0, 23.2, 25.6, 76.7, 135.6, 152.2, 82.6, 40.0, 26.4, 13.3]
            ],
            [
                'name' => 'Serie 2',
                'type' => 'bar',
                'data' => [12.6, 15.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 88.7, 48.8, 26.0, 12.3]
            ]
        ]);
    }
}
