<?php

namespace Illuminate\Utils;

use Illuminate\Traits\MemorySingletonTrait;
use Illuminate\Services\AppService;

class HelperCurrency
{
    use MemorySingletonTrait;

    public $appService;

    public function __construct()
    {
        $this->appService = AppService::getInstance();
    }
    /**
     * Get current currency
     * @return mixed
     */
    public function getCurrentCurrency()
    {
        $currentCurrency    = $this->getCookie('transcy_current_currency');
        if (empty($currentCurrency)) {
            $currentCurrency    = get_option('woocommerce_currency', '');
        }

        if (!array_key_exists($currentCurrency, getListCurrency())) {
            $currentCurrency = get_option('woocommerce_currency');
        }

        return $currentCurrency;
    }

    /**
     * Set currency in Cookie
     *
     * @param $currency_code
     * @param bool $checkout
     */
    public function setCurrentCurrency($currencyCode)
    {
        if ($currencyCode) {
            $this->setCookie('transcy_current_currency', $currencyCode, time() + 60 * 60 * 24, '/');
        }
    }

    /**
     * Set Cookie or Session
     *
     * @param $name
     * @param $value
     * @param int $time
     * @param string $path
     */
    public function setCookie($name, $value, $time = 86400, $path = '/')
    {
        if (isset($_SESSION[$name])) {
            @session_start();
            $_SESSION[$name] = $value;
            session_write_close();
        } else {
            @setcookie($name, $value, $time, $path);
            $_COOKIE[$name] = $value;
        }
    }

    /**
     * Get Cookie or Session
     *
     * @param $name
     *
     * @return bool
     */
    public static function getCookie($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
    }
}
