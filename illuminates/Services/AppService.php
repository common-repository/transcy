<?php

namespace Illuminate\Services;

use Illuminate\Utils\Helper;

class AppService extends BaseService
{
    /**
     * Praram token service with app
     * @param array
     */
    protected array $paramToken = [];

    /**
     * Language code with client
     * @param string
     */
    public string $defaultLang = '';

    /**
     * Language code with client
     * @param array
     */
    public array $advancedLang = [];

    /**
     * Basic code with client
     * @param array
     */
    public array $basicLang = [];

    /**
     * Currencies with client
     * @param array
     */
    public array $currencies = [];

    /**
     * Currencies with client
     * @param array
     */
    public array $inforClient = [];


    /**
     * Client Data
     * @param object
     */
    public object $clientData;

    /**
     * Default currency
     * @param array
     */
    public array $defaultCurrency  = [];

    /**
     * List currency
     * @param array
     */
    public array $listCurrency = [];


    /**
     * List Api App
     * @param object
     */
    protected array $appEndpoint = [
        'store-front'       => 'api/store-front',
        'register'          => 'api/register',
        'update-infor'      => 'api/clients/update-info',
        'uninstalled'       => 'api/webhooks/uninstalled',
        'deactived'         => 'api/webhooks/installation',
        'change-resource'   => 'api/webhooks/plugin/change-resource'
    ];


    public function __construct()
    {
        $this->endpoint     = Helper::getAppApiUrl();
        $this->paramToken   = [
            'Authorization'       => sprintf("Bearer %s", $this->generateToken()),
            'x-transcy-hostname'  => Helper::getDomain()
        ];

        //Get language
        $this->getLanguages();

        //Get language
        $this->getCurrency();

        //Get Inforclient
        $this->inforClient = [
            "status"                    => 1,
            "domain"                    => Helper::getDomain(),
            "email"                     => Helper::getEmail(),
            'default_language_code'     => Helper::geLocale(),
            'default_currency_code'     => Helper::getDefaultCurrency(),
            'woo_version'               => Helper::getWooVersion(),
            'tc_version'                => Helper::getTranscyVersion(),
            'wp_version'                => Helper::getWPVersion(),
            'php_version'               => Helper::getPHPVersion(),
            'theme_name'                => Helper::getThemeName(),
            'permalink_structure'       => Helper::getPermalinkStructure(),
        ];
    }

    public function getClientData()
    {
        if (empty($this->clientData)) {
            $responseBody = $this->get($this->appEndpoint['store-front'], $this->paramToken);
            if (!empty($responseBody)) {
                update_option('transcy_client_data', $responseBody);
                $this->clientData = $responseBody;
                return $this->clientData;
            }
            $responseBody = get_option('transcy_client_data');
            if (!empty($responseBody) && is_object($responseBody)) {
                $this->clientData = $responseBody;
                return $this->clientData;
            }
            return null;
        }
        return $this->clientData;
    }

    public function getLanguages()
    {
        if (empty($this->getClientData())) {
            return false;
        }

        $this->defaultLang = $this->getClientData()->data->default_language_code;

        foreach ($this->getClientData()->data->languages as $languages) {
            if ($languages->type == 2) {
                $this->advancedLang[] = $languages->locale;
            }
            if ($languages->type == 1 && $languages->locale != $this->defaultLang) {
                $this->basicLang[]   = $languages->locale;
            }
        }
        return true;
    }

    public function getCurrency()
    {
        if (empty($this->getClientData())) {
            return false;
        }

        if (!empty($listCurrency = $this->getClientData()->data->currencies)) {
            foreach ($listCurrency as $currency) {
                if (!$currency->is_published && !$currency->is_default) {
                    continue;
                }
                $item = [
                    'code'                  => $currency->code,
                    'symbol'                => $currency->settings->symbol,
                    'flag_code'             => $currency->settings->flag_code,
                    'custom_code'           => $currency->settings->custom_code,
                    'exchange_rate_type'    => $currency->settings->exchange_rate_type,
                    'exchange_rate_value'   => $currency->settings->exchange_rate_value,
                    'price_rounding_type'   => $currency->settings->price_rounding_type,
                    'price_rounding_value'   => $currency->settings->price_rounding_value,
                ];
                //Set Default Currency
                if ($currency->is_default) {
                    $this->defaultCurrency[$currency->code]  = $item;
                }

                //Set List
                $this->listCurrency[$currency->code]  = $item;
            }
        }
    }


    //Tracking Data
    public function trackingRegister()
    {
        if (!Helper::isRegistered()) {
            //Add api key
            if (empty(get_option('transcy_apikey', false))) {
                update_option('transcy_apikey', md5(strtotime("now")));
            }
            //Add transcy key
            $response = $this->post($this->appEndpoint['register'], [], $this->inforClient);
            if (isset($response->code) && $response->code == 200 && isset($response->message) && $response->message == 'OK') {
                if (empty(get_option('transcy_app_apikey', false))) {
                    update_option('transcy_app_apikey', $response->data->app_key);
                }
                update_option('_transcy_registered_site', 'registered');
            }
        }
    }

    public function trackingUpdateInfor()
    {
        if (
            Helper::isActiveOnApp()
            && isset($this->getClientData()->data)
            && ($this->getClientData()->data->app_version != $this->inforClient['tc_version']
                || $this->getClientData()->data->woo_version != $this->inforClient['woo_version']
                || $this->getClientData()->data->platform_version != $this->inforClient['wp_version']
                || !isset($this->getClientData()->data->php_version)
                || $this->getClientData()->data->php_version != $this->inforClient['php_version']
                || $this->getClientData()->data->theme_name != $this->inforClient['theme_name']
                || $this->getClientData()->data->permalink_structure != $this->inforClient['permalink_structure']
            )
        ) {
            $body = [
                'woo_version'           => $this->inforClient['woo_version'],
                'app_version'           => $this->inforClient['tc_version'],
                'platform_version'      => $this->inforClient['wp_version'],
                'php_version'           => $this->inforClient['php_version'],
                'theme_name'            => $this->inforClient['theme_name'],
                'permalink_structure'   => $this->inforClient['permalink_structure']
            ];
            return $this->put($this->appEndpoint['update-infor'], $this->paramToken, $body);
        }
    }

    public function trackingUninstalled()
    {
        $body = [
            "domain" => $this->inforClient['domain'],
            "status" => 3
        ];
        return $this->post($this->appEndpoint['uninstalled'], $this->paramToken, $body);
    }

    public function trackingDeactive()
    {
        $body = [
            "domain" => $this->inforClient['domain'],
            "status" => 2
        ];
        return $this->post($this->appEndpoint['deactived'], $this->paramToken, $body);
    }

    public function getPlanApp()
    {
        if (isset($this->getClientData()->data) && isset($this->getClientData()->data->app_plan) && !empty($plan = $this->getClientData()->data->app_plan)) {
            return $plan;
        }
        return false;
    }

    public function changeResource($body = [])
    {
        return $this->post($this->appEndpoint['change-resource'], $this->paramToken, $body);
    }

    public function getLocationSwitcher()
    {
        if (isset($this->getClientData()->data->settings->switcher->general->position)) {
            $location = $this->getClientData()->data->settings->switcher->general->position;
            if ($location->preference == 'embedded' && isset($location->wp_menu_position->slug) && !empty($location->wp_menu_position->slug)) {
                return [
                    'slug'      => $location->wp_menu_position->slug,
                    'position'  => $location->wp_menu_position->position
                ];
            }
        }
        return null;
    }

    public function isSwicherTypeFriendly()
    {
        $permalinkStructure = Helper::getPermalinkStructure();
        if($permalinkStructure == 'friendly' && isset($this->getClientData()->data->url_format) && $this->getClientData()->data->url_format == 2){
            return true;
        }
        return false;
    }

    public function getListLang(){
        return array_merge([$this->defaultLang], $this->advancedLang, $this->basicLang);
    }
}
