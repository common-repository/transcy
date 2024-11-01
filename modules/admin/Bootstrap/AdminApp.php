<?php

namespace TranscyAdmin\Bootstrap;

use Illuminate\Interfaces\IApplication;

class AdminApp implements IApplication
{
    /**
     * Bootstrapping application
     *
     * @return
     */
    private $hooks = [
        \TranscyAdmin\Hooks\Menu::class,
        \TranscyAdmin\Hooks\Enqueue::class,
        \TranscyAdmin\Hooks\Cores\Admin::class,
        \TranscyAdmin\Hooks\Cores\Active::class,
        \TranscyAdmin\Hooks\Cores\Deactive::class,
        \TranscyAdmin\Hooks\Cores\Upgrader::class,
        \TranscyAdmin\Hooks\Cores\Uninstall::class,
        \TranscyAdmin\Hooks\Resources\Post::class,
        \TranscyAdmin\Hooks\Resources\Term::class,
        \TranscyAdmin\Hooks\Resources\Menu::class
    ];

    /**
     * Bootstrapping application
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
