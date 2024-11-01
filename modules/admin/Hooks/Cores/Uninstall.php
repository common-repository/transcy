<?php

namespace TranscyAdmin\Hooks\Cores;

use Illuminate\Interfaces\IHook;
use Illuminate\Services\AppService;
use Illuminate\Utils\HelperTranslations;

class Uninstall implements IHook
{
    public function registerHooks()
    {
        //Active plugin
        register_uninstall_hook(TRANSCY_FILE, [__CLASS__, 'uninstall']);
    }

    /**
     * Uninstall plugin
     *
     * @return array
     */
    public static function uninstall()
    {
        $appService = AppService::getInstance();
        $appService->trackingUninstalled();

        //Reactive
        $translate = HelperTranslations::getInstance();
        $translate->uninstall();

        //Delete option name
        $translate->deleteOption();
    }
}
