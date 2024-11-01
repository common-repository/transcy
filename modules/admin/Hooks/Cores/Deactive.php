<?php

namespace TranscyAdmin\Hooks\Cores;

use Illuminate\Interfaces\IHook;
use Illuminate\Services\AppService;
use Illuminate\Utils\HelperTranslations;

class Deactive implements IHook
{
    public function registerHooks()
    {
        //Active plugin
        register_deactivation_hook(TRANSCY_FILE, [__CLASS__, 'deactive']);
    }

    /**
     * Deactive plugin
     *
     * @return array
     */
    public static function deactive()
    {
        $appService = AppService::getInstance();
        $appService->trackingDeactive();

        $translate = HelperTranslations::getInstance();
        $translate->deactive();

        delete_option('_transcy_registered_site');
    }
}
