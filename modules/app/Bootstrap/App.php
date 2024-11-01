<?php

namespace TranscyApp\Bootstrap;

use Illuminate\Interfaces\IApplication;
use Illuminate\Utils\Helper;

class App implements IApplication
{
    /**
     * Bootstrapping application
     *
     * @return
     */
    private $hooks = [
        \TranscyApp\Hooks\Post::class,
        \TranscyApp\Hooks\Term::class
    ];

    /**
     * init application
     *
     * @return
     */
    public function init()
    {
        foreach ($this->hooks as $hook) {
            (new $hook())->registerHooks();
        }
    }
}
