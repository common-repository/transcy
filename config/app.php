<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Modules
    |--------------------------------------------------------------------------
    |
    | The modules listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */
    'modules' => [

        //admin page
        'admin' => [
            \TranscyAdmin\Bootstrap\AdminApp::class
        ],

        //api page
        'api' => [
            TranscyApp\Bootstrap\App::class,
        ],

        //front page
        'front' => [
            TranscyFront\Bootstrap\Front::class,
        ]
    ]
];
