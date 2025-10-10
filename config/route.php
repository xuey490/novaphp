<?php

return [
    'GET' => [
        '/' => 'HomeController@index',
        '/home/show/{id:\d+}' => 'HomeController@show'
    ],
    'POST' => [
        '/submit' => 'HomeController@submit'
    ]
];