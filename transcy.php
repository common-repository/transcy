<?php
/*
Plugin Name: Transcy
Plugin URI: https://onecommerce.io/transcy/?utm_source=transcyphp&utm_medium=plugin-uri&utm_campaign=wordpress-tc
Description: Make your website multilingual easily with no coding. Instantly translate between languages with the Google Translate API.
Version: 2.12.2
Author: OneCommerce
Author URI: https://onecommerce.io/transcy/?utm_source=transcyphp&utm_medium=author-uri&utm_campaign=wordpress-tc
Text Domain: transcy
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$transcyUrl = untrailingslashit(plugin_dir_url(__FILE__));

if (!defined('TRANSCY_PATH')) {
    define('TRANSCY_PATH', __DIR__);
}

define('TRANSCY_FILE', __FILE__);

define('TRANSCY_DIR_PATH', plugin_dir_path(__FILE__));

define('TRANSCY_ASSETS_FOLDER_PATH', TRANSCY_DIR_PATH . 'assets');

define('TRANSCY_REL_PATH', basename(dirname(__FILE__)));

define('TRANSCY_PLUGIN_URL', $transcyUrl);

define('TRANSCY_LOCALE', get_locale());

define('TRANSCY_URL', plugins_url(basename(dirname(__FILE__))));

define('TRANSCY_ASSETS_FOLDER', TRANSCY_URL . '/assets');

require_once TRANSCY_PATH . '/vendor/autoload.php';

$spine = new \Illuminate\Bootstrap\App();
$spine->init();