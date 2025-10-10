<?php

return [
    'default' => 'single',
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path'   => BASE_PATH . '/storage/logs/app.log'
        ]
    ]
];