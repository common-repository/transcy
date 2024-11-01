<?php
if (!function_exists('getInforClient')) {
    /**
     * Return lang default
     *
     * @since 4.4.0
     * @var string
     */
    function getInforClient()
    {
        return $GLOBALS['appService']->inforClient;
    }
}

if (!function_exists('getDefaultLang')) {
    /**
     * Return lang default
     *
     * @since 4.4.0
     * @var string
     */
    function getDefaultLang()
    {
        return $GLOBALS['appService']->defaultLang;
    }
}

if (!function_exists('getBasicLang')) {
    /**
     * Return lang default
     *
     * @since 4.4.0
     * @var string
     */
    function getBasicLang()
    {
        return $GLOBALS['appService']->basicLang;
    }
}

if (!function_exists('getAdvancedLang')) {
    /**
     * Return advanced lang
     *
     * @since 4.4.0
     * @var array
     */
    function getAdvancedLang()
    {
        return $GLOBALS['appService']->advancedLang;
    }
}


if (!function_exists('getPlanApp')) {
    /**
     * Return current request is spine api or not
     *
     * @since 4.4.0
     * @var array
     */
    function getPlanApp()
    {
        return $GLOBALS['appService']->getPlanApp();
    }
}

if (!function_exists('getDefaultCurrency')) {
    /**
     * Return lang default
     *
     * @since 4.4.0
     * @var string
     */
    function getDefaultCurrency()
    {
        return $GLOBALS['appService']->defaultCurrency;
    }
}

if (!function_exists('getListCurrency')) {
    /**
     * Return lang default
     *
     * @since 4.4.0
     * @var string
     */
    function getListCurrency()
    {
        return $GLOBALS['appService']->listCurrency;
    }
}

if (!function_exists('isSwicherTypeFriendly')) {
    /**
     * Return lang default
     *
     * @since 4.4.0
     * @var string
     */
    function isSwicherTypeFriendly()
    {
        return $GLOBALS['appService']->isSwicherTypeFriendly();
    }
}

if (!function_exists('getListLang')) {
    /**
     * Return lang default
     *
     * @since 4.4.0
     * @var string
     */
    function getListLang()
    {
        return $GLOBALS['appService']->getListLang();
    }
}

if (!function_exists('getLocationSwitcher')) {
    /**
     * Return location swithcer
     *
     * @since 4.4.0
     * @var string
     */
    function getLocationSwitcher()
    {
        return $GLOBALS['appService']->getLocationSwitcher();
    }
}

if (!function_exists('isMethodGetApi')) {
    /**
     * Return current request is spine api or not
     *
     * @since 4.4.0
     * @var array
     */
    function isMethodGetApi()
    {
        return \Illuminate\Utils\Helper::isMethodGetApi();
    }
}

if (!function_exists('isSpineApi')) {
    /**
     * Return current request is spine api or not
     *
     * @since 4.4.0
     * @var array
     */
    function isSpineApi()
    {
        return \Illuminate\Utils\Helper::isSpineApi();
    }
}

if (!function_exists('spineBodyParams')) {
    /**
     * Retrive body param
     *
     * @since transcy
     *
     * @return string
     */
    function spineBodyParams()
    {
        return wp_unslash($_REQUEST);
    }
}

if (!function_exists('spineParam')) {
    /**
     * Retrive body param
     *
     * @since transcy
     *
     * @return string
     */
    function spineParam(string $key, $default = '')
    {
        $input = spineBodyParams();

        return isset($input[$key]) ? $input[$key] : $default;
    }
}
