<?php

namespace TranscyAdmin\Hooks\Cores;

use Illuminate\Interfaces\IHook;
use Illuminate\Utils\Helper;
use TranscyAdmin\Hooks\Cores\Upgrader;
use TranscyAdmin\Hooks\Cores\Active;
use Illuminate\Utils\HelperTranslations;

class Admin implements IHook
{
    public function registerHooks()
    {
        //register menu
        add_action('admin_init', array($this, 'adminInit'));
    }

    public function adminInit()
    {
        //Active
        $active  = new Active();
        $active->active();

        //Reactive
        $translate = HelperTranslations::getInstance();
        $translate->reActive();

        if (Helper::isActiveOnApp()) {
            $upgrader = new Upgrader();
            $upgrader->upgrader();
        }
    }
}
