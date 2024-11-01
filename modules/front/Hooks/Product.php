<?php

namespace TranscyFront\Hooks;

use \Illuminate\Interfaces\IHook;
use \Illuminate\Utils\HelperCurrency;
use \Illuminate\Utils\Helper;
use \Illuminate\Utils\HelperTranslations;

class Product implements IHook
{
    protected $langCurrent;

    protected $langDefault;

    protected $currencyCurrent;

    protected $currencyDefault;

    protected $helperCurrency;

    protected $currencyList;

    public function __construct()
    {
        $this->langCurrent         = HelperTranslations::getLangCurrent();

        $this->langDefault         = getDefaultLang();

        $this->helperCurrency     = HelperCurrency::getInstance();

        $this->currencyCurrent    = $this->helperCurrency->getCurrentCurrency();

        $this->currencyDefault    = getDefaultCurrency();

        $this->currencyList       = getListCurrency();
    }

    public function registerHooks()
    {
        if (Helper::getWooStatus()) {
            add_filter('woocommerce_currency_symbol', array($this, 'currencySymbol'), 99, 1);

            if (!$this->sameCurrencyDefault()) {
                // woo hook price
                add_filter('woocommerce_product_get_price', [$this, 'changeProductPriceConvert'], 99, 1);
                add_filter('woocommerce_product_get_regular_price', [$this, 'changeProductPriceConvert'], 99, 1);
                add_filter('woocommerce_product_get_sale_price', [$this, 'changeProductPriceConvert'], 99, 1);

                /*Variable price*/
                add_filter('woocommerce_product_variation_get_price', array($this, 'changeProductPriceConvert'), 99, 1);
                add_filter('woocommerce_product_variation_get_regular_price', array($this, 'changeProductPriceConvert'), 99, 1);
                add_filter('woocommerce_product_variation_get_sale_price', array($this, 'changeProductPriceConvert'), 99, 1);

                /*Variable Parent min max price*/
                add_filter('woocommerce_variation_prices', array($this, 'getWoocommerceVariationPrices'), 99, 1);

                /**
                 * Format price
                 */
                add_filter('wc_get_price_decimals', array($this, 'setDecimals'), PHP_INT_MAX);
            }

            add_action('woocommerce_cart_loaded_from_session', array($this, 'beforeMiniCart'), 99);

            add_action('woocommerce_before_checkout_process', array($this, 'beforeCheckoutProcess'), 99);

            add_action('wp_enqueue_scripts', array($this, 'removeSession'));

            add_filter('post_type_archive_link', array($this, 'archiveLink'), 999, 1);
            add_filter('woocommerce_get_cart_url', array($this, 'archiveLink'), 999, 1);
            add_filter('woocommerce_get_checkout_url', array($this, 'archiveLink'), 999, 1);
            add_filter('woocommerce_get_shop_page_permalink', array($this, 'pagePermalink'), PHP_INT_MAX, 1);
            add_filter('woocommerce_add_to_cart_redirect', array($this, 'addToCartRedirect'), PHP_INT_MAX, 1);
        }
    }

    public function currencySymbol($symbol)
    {
        if (!$this->sameCurrencyDefault() && array_key_exists($this->currencyCurrent, $this->currencyList)) {
            return $this->currencyList[$this->currencyCurrent]['symbol'];
        }
        return $symbol;
    }

    public function changeProductPriceConvert($price)
    {
        if (empty($price)) {
            return $price;
        }
        if (!$this->sameCurrencyDefault() && array_key_exists($this->currencyCurrent, $this->currencyList)) {
            $dataCurrentActive  = $this->currencyList[$this->currencyCurrent];
            if (array_key_exists("USD", $this->currencyDefault)) {
                $exchangeRate   = $dataCurrentActive['exchange_rate_value'];
            } else {
                $exchangeRate   = $dataCurrentActive['exchange_rate_value'] / reset($this->currencyDefault)['exchange_rate_value'];
            }

            $price = (float)$price * (float)$exchangeRate;
        }

        //Rounding Price
        return $this->formatPrice($price, $dataCurrentActive);
    }

    public function getWoocommerceVariationPrices($priceArr)
    {
        foreach ($priceArr as $priceType => $values) {
            foreach ($values as $key => $price) {
                if (!$this->sameCurrencyDefault() && array_key_exists($this->currencyCurrent, $this->currencyList)) {
                    $dataCurrentActive  = $this->currencyList[$this->currencyCurrent];
                    if (array_key_exists("USD", $this->currencyDefault)) {
                        $exchangeRate   = $dataCurrentActive['exchange_rate_value'];
                    } else {
                        $exchangeRate   = $dataCurrentActive['exchange_rate_value'] / reset($this->currencyDefault)['exchange_rate_value'];
                    }

                    $price = (float)$price * (float)$exchangeRate;
                }
                $priceArr[$priceType][$key] = $this->formatPrice($price, $dataCurrentActive);
            }
        }
        return $priceArr;
    }

    /**
     * @param $decimal
     *
     * @return int
     */
    public function setDecimals($decimal)
    {
        $dataCurrentActive  = $this->currencyList[$this->currencyCurrent];
        if (!empty($dataCurrentActive)) {
            switch ($dataCurrentActive['price_rounding_type']) {
                case '2':
                case '3':
                    return 0;
                case '4':
                    //Custom
                    $roundingValue = explode('.', $dataCurrentActive['price_rounding_value']);
                    return (int)strlen(end($roundingValue));
            }
        }
        return (int) $decimal;
    }

    public function beforeMiniCart()
    {
        @WC()->cart->calculate_totals();
    }

    public function beforeCheckoutProcess()
    {
        $this->helperCurrency->setCurrentCurrency(array_key_first($this->currencyDefault));
        @WC()->cart->calculate_totals();
    }

    public function removeSession()
    {
        $ver = time();
        wp_enqueue_script('transcy-multi-currency-cart', TRANSCY_ASSETS_FOLDER . '/js/transcy-multi-currency-cart.js', array('jquery'), $ver);
    }

    public function formatPrice($price, $currencyActive)
    {
        switch ($currencyActive['price_rounding_type']) {
            case '2':
                //Round Up
                return ceil($price);
            case '3':
                //Round Down
                return floor($price);
            case '4':
                return intval($price) + $currencyActive['price_rounding_value'];
        }
        return $price;
    }

    public function sameCurrencyDefault()
    {
        if (array_key_exists($this->currencyCurrent, $this->currencyDefault)) {
            return true;
        }
        return false;
    }

    public function archiveLink(string $link)
    {
        $language = HelperCurrency::getCookie('transcy_switcher_language');

        if (isSwicherTypeFriendly()) {
            if (!empty($language)) {
                $this->langCurrent = $language;
            }
            if ($this->langCurrent != $this->langDefault) {
                $domain = Helper::getDomain();
                $link   = str_replace(sprintf("%s/", $domain), sprintf("%s/%s/", $domain, $this->langCurrent), $link);
            }
            return $link;
        }

        $paramQuery = [];
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            $paramQuery['lang'] = $_GET['lang'];
        }
        $link = add_query_arg($paramQuery, $link);
        return $link;
    }

    public function pagePermalink($link)
    {
        $language = HelperCurrency::getCookie('transcy_switcher_language');
        if (!empty($language) && $language != $this->langDefault) {
            if (isSwicherTypeFriendly()) {
                $domain = Helper::getDomain();
                $link   = str_replace(sprintf("%s/", $domain), sprintf("%s/%s/", $domain, $language), $link);
                return $link;
            } else {
                $paramQuery['lang'] = $language;
                $link               = add_query_arg($paramQuery, $link);
                return $link;
            }
        }
        return $link;
    }

    public function addToCartRedirect($url)
    {
        if (!isset($_REQUEST['add-to-cart']) || !is_numeric($_REQUEST['add-to-cart'])) {
            return $url;
        }
        if(empty($url)){
            $url = get_permalink($_REQUEST['add-to-cart']);
        }
        
        return $this->pagePermalink($url);
    }
}
