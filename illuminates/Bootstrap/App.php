<?php

namespace Illuminate\Bootstrap;

use Illuminate\Exceptions\Handler;
use Illuminate\Interfaces\IApplication;
use Illuminate\Utils\Helper;

class App implements IApplication
{
    /**
     * Bootstrapping global application don't support WP_CLI
     *
     * @return
     */
    private $hooks = [
        \Illuminate\Hooks\REST\WPRoute::class,
        \Illuminate\Hooks\Product::class,
        \Illuminate\Hooks\RewriteRules::class
    ];

    /**
     * Set locale
     */
    private function setLocale()
    {
        setlocale(LC_ALL, 'en_PH.utf8');
    }

    /**
     * Bootstrapping application
     *
     * @return
     */
    private function bootstrapping()
    {
        foreach ($this->hooks as $hook) {
            (new $hook())->registerHooks();
        }
    }

    /**
     * init application
     *
     * @return
     */
    public function init()
    {

        //Set locale
        $this->setLocale();

        //load with default
        $this->bootstrapping();

        if (defined('WP_CLI') && WP_CLI) {
            return;
        }

        //register exception handler
        Handler::getInstance()->register();

        try {
            $config = include TRANSCY_PATH . '/config/app.php';

            if (Helper::isActiveOnApp()) {
                $GLOBALS['appService'] = \Illuminate\Services\AppService::getInstance();
                require_once TRANSCY_PATH . '/helpers.php';

                //register api route
                $this->api($config);

                //register frontend
                $this->front($config);
            }
            //register admin
            $this->admin($config);
        } catch (\Throwable $exception) {
            //return handle
            return Handler::getInstance()->render($exception);
        }
    }

    /**
     * Init api area
     *
     * @return
     */
    private function api($config)
    {
        if (is_admin() || !isset($config['modules']['api'])) {
            return;
        }

        if (!isSpineApi()) {
            return;
        }

        foreach ($config['modules']['api'] as $index => $class) {
            (new $class())->init();
        }
    }

    /**
     * Init front area
     *
     * @return
     */
    private function front($config)
    {
        if (is_admin() || !isset($config['modules']['front']) || isSpineApi()) {
            return;
        }

        foreach ($config['modules']['front'] as $index => $class) {
            (new $class())->init();
        }
    }

    /**
     * Init admin area
     *
     * @return
     */
    private function admin($config)
    {
        if (!is_admin() || !isset($config['modules']['admin'])) {
            return;
        }

        foreach ($config['modules']['admin'] as $index => $class) {
            (new $class())->init();
        }
    }
}
