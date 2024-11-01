<?php

namespace Illuminate\Utils;

use Illuminate\Configuration;

class Helper
{
    /**
     * Return HTTP_HOST
     *
     * @return string
     */
    public static function getHost()
    {
        $host = self::siteUrl();

        return self::urlHost($host);
    }

    /**
     * Return domain
     *
     * @return string
     */
    public static function getDomain()
    {
        $domain = preg_replace("~^www\.~", "", self::getHost());

        return $domain;
    }

    /**
     * Return Url Host
     *
     * @param string $url
     *
     * @return string
     */
    public static function urlHost($url)
    {
        if (empty($url)) {
            return '';
        }

        return parse_url($url, PHP_URL_HOST);
    }

    /**
     * Return Site Url
     *
     * @param string $url
     *
     * @return string
     */
    public static function siteUrl()
    {
        if (function_exists('site_url')) {
            return site_url();
        }
    }


    /**
     * Check current is spine api
     *
     * @return string
     */
    public static function isSpineApi()
    {
        $requestUri = esc_url(($_SERVER['REQUEST_URI'] ?? ''));

        if (strpos($requestUri, 'wp-json/transcy-api') !== false || strpos($requestUri, 'rest_route=/transcy-api') !== false) {
            return true;
        }

        return false;
    }


    /**
     * Check current is spine api
     *
     * @return string
     */
    public static function isMethodGetApi()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';

        if (strtolower($method) == 'get') {
            return true;
        }

        return false;
    }

    /**
     * Convert absolute url to relative url
     *
     * @param string $url
     *
     * @return string
     */
    public static function relativeUrl($url)
    {
        return preg_replace('/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', '' . $url);
    }

    /**
     * is production?
     *
     * @return bool
     */
    public static function isProduction()
    {
        if (file_exists(TRANSCY_DIR_PATH . '/env.php')) {
            if (!defined("CRISP_ID") && !defined("APP_API_URL") && !defined("APP_URL") && !defined("APP_BUILD")) {
                include(TRANSCY_DIR_PATH . '/env.php');
            }
            return false;
        }
        return true;
    }

    /**
     * Get current locale
     *
     * @return string
     */
    public static function getLocale()
    {
        return get_locale();
    }

    /**
     * Get value in array
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     *
     * @return string
     */
    public static function arrayGet($array, $key, $default = '')
    {
        if (isset($array[$key])) {
            return empty($array[$key]) ? $default : $array[$key];
        }

        return $default;
    }

    /**
     * Get route
     *
     * @param WP_REST_Request $request
     *
     * @return string
     */
    public static function getRoute($request, $prefix = '/api/')
    {
        return str_replace($prefix, '', $request->get_route());
    }

    /**
     * Get last slug in  url
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     *
     * @return string
     */
    public static function lastSlug(string $slug, $shouldTrim = false): string
    {
        if ($shouldTrim) {
            $slug = rtrim($slug, '/');
        }

        $slug = explode('/', $slug);

        return end($slug);
    }

    /**
     * Convert timestamp to time ago
     *
     * @param string $postDateTime
     *
     * @return string
     */
    public static function humanTime($postDateTime, $returnDate = true)
    {
        $timestamp    = strtotime($postDateTime);
        $currentTime  = time();
        $timeElapsed  = $currentTime - $timestamp;
        $seconds      = $timeElapsed;
        $minutes      = round($timeElapsed / 60);
        $hours     = round($timeElapsed / 3600);
        $days      = round($timeElapsed / 86400);
        $weeks     = round($timeElapsed / 604800);
        $months    = round($timeElapsed / 2600640);
        $years     = round($timeElapsed / 31207680);

        // Seconds
        if ($returnDate) {
            if ($seconds <= 60) {
                return __('just now', 'transcy');
            } elseif ($minutes <= 60) {  //Minutes
                return sprintf(__('%s min ago', 'transcy'), $minutes);
            } elseif ($hours <= 24) { //Hours
                return sprintf(__('%s hours ago', 'transcy'), $hours);
            } elseif ($days <= 7) { //Days
                if ($days == 1) {
                    return __('Yesterday', 'transcy');
                }
                return sprintf(__('%s days ago', 'transcy'), $days);
            } elseif ($weeks <= 4.3) { //Weeks
                if ($weeks == 1) {
                    return __('a week ago', 'transcy');
                }
                return sprintf(__('%s weeks ago', 'transcy'), $weeks);
            } elseif ($months <= 12 && date('Y', $timestamp) == date('Y', $currentTime)) { //Months
                return strpos(self::getLocale(), 'en_') !== false
                    ? date('M d', $timestamp)
                    : date('d/m/Y', $timestamp);
            }
        }
        //Years
        return strpos(self::getLocale(), 'en_') !== false
            ? date('M d, Y', $timestamp)
            : date('d/m/Y', $timestamp);
    }

    /**
     * Html entity decode
     *
     * @param string $text
     *
     * @return string
     */
    public static function entityDecode($text)
    {
        return html_entity_decode($text);
    }

    /**
     * Check current url is rest api
     *
     * @return bool
     */
    public static function isRestApiRequest()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $rest_prefix = trailingslashit(rest_get_url_prefix());
        $is_rest_api_request = strpos($_SERVER['REQUEST_URI'], $rest_prefix) !== false;

        return apply_filters('is_rest_api_request', $is_rest_api_request);
    }


    /**
     * Called by allow_excerpt_html
     * logic for the excerpt filter allowing the currently selected tag.
     *
     * @param string $text - excerpt string
     * @return string $text - the new excerpt
     */
    public static function excerpt($text)
    {
        // reproduces wp_trim_excerpt filter, preserving the excerpt_more and excerpt_length filters
        if (!empty($text)) {
            $text = str_replace('\]\]\>', ']]&gt;', $text);
            $text = strip_tags($text);

            // use the defined length, if already applied...
            $excerpt_length = 55;
            $words = explode(' ', $text, $excerpt_length + 1);
            if (count($words) > $excerpt_length) {
                array_pop($words);
                array_push($words, '[&hellip;]');
                $text = implode(' ', $words);
            }

            $text = self::entityDecode($text);
        }

        return $text;
    }

    /**
     * Retrive the language code
     *
     * @return string
     */
    public static function languageCode()
    {
        $lang = self::getLocale();
        $exp = explode('_', $lang);
        return (count($exp) > 1) ? $exp[1] : $lang;
    }

    /**
     * Check current request is preview?
     *
     * @return bool
     */
    public static function isPreview($post)
    {
        return (bool)($_GET['preview'] ?? false);
    }

    /**
     * Support cors header
     *
     * @return bool
     */
    public static function corsHeaders()
    {
        $allow_headers = array(
            'Authorization',
            'X-WP-Nonce',
            'Content-Disposition',
            'Content-MD5',
            'Content-Type',
            'request-startTime'
        );

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: " . implode(', ', $allow_headers));
    }

    /**
     * Get env 
     *
     * @return string
     */
    public static function getEnv()
    {
        if (self::isProduction()) {
            return Configuration::APP_BUILD;
        }
        return APP_BUILD;
    }

    /**
     * Get env 
     *
     * @return string
     */
    public static function getSuffix()
    {
        if (in_array(self::getEnv(), ['dev', 'stag', 'local'])) {
            return sprintf('-%s', self::getEnv());
        }
        return '';
    }

    /**
     * Get env 
     *
     * @return string
     */
    public static function getCrispID()
    {
        if (self::isProduction()) {
            return Configuration::CRISP_ID;
        }
        return CRISP_ID;
    }

    /**
     * Get app url 
     *
     * @return string
     */
    public static function getAppUrl()
    {
        if (self::isProduction()) {
            return Configuration::APP_URL;
        }
        return APP_URL;
    }

    /**
     * Get app url 
     *
     * @return string
     */
    public static function getAppApiUrl()
    {
        if (self::isProduction()) {
            return Configuration::APP_API_URL;
        }
        return APP_API_URL;
    }

    /**
     * Get app url 
     *
     * @return string
     */
    public static function getTranscyVersion()
    {
        return get_file_data(TRANSCY_DIR_PATH . 'transcy.php', array('Version'), 'plugin')[0];
    }

    /**
     * Get Woo Version
     *
     * @return string
     */
    public static function getWooVersion()
    {
        if (!self::getWooStatus()) {
            return null;
        }
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugins();
        if (isset($plugin_data['woocommerce/woocommerce.php'])) {
            return $plugin_data['woocommerce/woocommerce.php']['Version'];
        }
        return null;
    }

    /**
     * Get Woo Status
     *
     * @return string
     */
    public static function getWooStatus()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }

    /**
     * Get WP Version
     *
     * @return string
     */
    public static function getWPVersion()
    {
        return get_bloginfo('version');
    }

    /**
     * Get Email
     *
     * @return string
     */
    public static function getEmail()
    {
        return get_option('admin_email');
    }

    /**
     * Get locale site
     *
     * @return string
     */
    public static function geLocale()
    {
        $locale     = get_locale();
        $explode    = explode('_', $locale);
        return $explode[0];
    }

    /**
     * Get default currency
     *
     * @return string
     */
    public static function getDefaultCurrency()
    {
        //Check active woocomerce
        if (!self::getWooStatus()) {
            return null;
        }

        if (!empty($currency = get_option('woocommerce_currency'))) {
            return $currency;
        }
        return null;
    }

    /**
     * Get resource post
     *
     * @return array
     */
    public static function getResourcePosts()
    {
        return Configuration::RESOURCES_POSTS;
    }

    /**
     * Get resource post
     *
     * @return array
     */
    public static function getResourceNoTranslate()
    {
        return Configuration::RESOURCES_NO_TRANSLATE;
    }

    /**
     * Get resource terms
     *
     * @return array
     */
    public static function getResourceTerms()
    {
        return Configuration::RESOURCES_TERMS;
    }

    /**
     * Get resource terms
     *
     * @return array
     */
    public static function isActiveOnApp()
    {
        if (empty(get_option('transcy_app_apikey', false))) {
            return false;
        }
        return true;
    }

    /**
     * Get resource terms
     *
     * @return array
     */
    public static function isRegistered()
    {
        if (!empty($registered = get_option('_transcy_registered_site', false)) && $registered == 'registered') {
            return true;
        }
        return false;
    }

    /**
     * Get resource terms
     *
     * @return array
     */
    public static function getPHPVersion()
    {
        return phpversion();
    }

    /**
     * Get resource terms
     *
     * @return array
     */
    public static function getThemeName()
    {
        $theme = wp_get_theme();
        return $theme->get('Name');
    }

    /**
     * Get resource terms
     *
     * @return array
     */
    public static function getPermalinkStructure()
    {
        $permalinkStructure = get_option( 'permalink_structure' );
        if(empty($permalinkStructure)){
            return 'plain';
        }
        return 'friendly';
    }
}
