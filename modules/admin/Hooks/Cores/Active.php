<?php

namespace TranscyAdmin\Hooks\Cores;

use Illuminate\Interfaces\IHook;
use Illuminate\Services\AppService;
use Illuminate\Database\Migrations\TranscyTranslationsTable;
use Illuminate\Utils\HelperTranslations;

class Active implements IHook
{
    public function registerHooks()
    {
        //Active plugin
        register_activation_hook(TRANSCY_FILE, array($this, 'active'));
    }

    /**
     * Active plugin
     *
     * @return array
     */
    public function active()
    {
        //Register table
        $table = new TranscyTranslationsTable();
        $table->up();

        //Tracking to app active
        $appService = AppService::getInstance();
        $appService->trackingRegister();
    }
}
