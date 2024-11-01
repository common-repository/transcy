<?php

namespace TranscyFront\Hooks;

use \Illuminate\Interfaces\IHook;
use Illuminate\Utils\Helper;

class Enqueue implements IHook
{
    protected $suffix;

    public function registerHooks()
    {
        $this->suffix = Helper::getSuffix();
        add_action('wp_enqueue_scripts', array( $this, 'addScript'), 99);
    }

    public function addScript()
    {
        $ver = time();
        wp_enqueue_script('transcy-switcher-js',  TRANSCY_ASSETS_FOLDER  . '/js/transcy-switcher' . $this->suffix . '.js', array('jquery'), $ver, true);
        wp_enqueue_style('transcy-switcher-css',  TRANSCY_ASSETS_FOLDER  . '/css/transcy-switcher' . $this->suffix . '.css', array(), $ver);
    }
}
