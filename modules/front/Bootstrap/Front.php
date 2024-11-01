<?php

namespace TranscyFront\Bootstrap;

use Illuminate\Interfaces\IApplication;

class Front implements IApplication
{
    /**
     * Bootstrapping application
     *
     * @return
     */
    private $hooks = [
        \TranscyFront\Hooks\Switcher::class,
        \TranscyFront\Hooks\Enqueue::class,
        \TranscyFront\Hooks\Post::class,
        \TranscyFront\Hooks\Term::class,
        \TranscyFront\Hooks\Product::class,
        \TranscyFront\Hooks\Menu::class
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
