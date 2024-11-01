<?php

namespace TranscyAdmin\Hooks\Cores;

use Illuminate\Interfaces\IHook;
use Illuminate\Services\AppService;
use Illuminate\Utils\HelperTranslations;
use Illuminate\Utils\Helper;
use Illuminate\Database\Migrations\TranscyTranslationsTable;

class Upgrader implements IHook
{
    public function registerHooks()
    {
        //Active plugin
        add_action('upgrader_process_complete', array($this, 'upgrader'), 99);
    }

    /**
     * Upgrader plugin
     *
     * @return array
     */
    public function upgrader()
    {
        //Push upadte version to app
        $appService = AppService::getInstance();
        $appService->trackingUpdateInfor();

        //upgrader version
        $this->upgraderVersion_2_11_1();

        //upgrader version
        $this->upgraderVersion_2_12_1();
    }

    /**
     * Upgrader plugin
     *
     * @return array
     */
    public function upgraderVersion_2_11_1()
    {
        $trackingUpgrader = (array)get_option('_transcy_upgrader_tracking');

        if (version_compare(Helper::getTranscyVersion(), '2.11.1', '>=') && version_compare(Helper::getTranscyVersion(), '2.11.3', '<=') && !in_array('2.11.1', $trackingUpgrader)) {
            //Create table
            $table = new TranscyTranslationsTable();
            $table->up();

            //Migrate Data
            $actionSeeders =  [
                \Illuminate\Database\Seeders\RelationshipSeeder::class
            ];
            foreach ($actionSeeders as $seeder) {
                (new $seeder())->run();
            }

            //sync resource to translate
            $translate = HelperTranslations::getInstance();
            $translate->syncResourceToTranslate();

            $trackingUpgrader[] = '2.11.1';
            update_option('_transcy_upgrader_tracking', $trackingUpgrader);
        }
    }

    /**
     * Upgrader plugin
     *
     * @return array
     */
    public function upgraderVersion_2_12_1()
    {
        $trackingUpgrader = (array)get_option('_transcy_upgrader_tracking');

        if (version_compare(Helper::getTranscyVersion(), '2.12.1', '>=') && !in_array('2.12.1', $trackingUpgrader)) {
            //Migrate Data
            $actionSeeders =  [
                \Illuminate\Database\Seeders\ClearTranslateOriginalSeender::class
            ];
            foreach ($actionSeeders as $seeder) {
                (new $seeder())->run();
            }

            $trackingUpgrader[] = '2.12.1';
            update_option('_transcy_upgrader_tracking', $trackingUpgrader);
        }
    }
}
