<?php

namespace Illuminate\Hooks\REST;

use Illuminate\Interfaces\IHook;
use Illuminate\Route\Route;
use Illuminate\Utils\Helper;
use Illuminate\Traits\ResponseTrait;

class WPRoute extends \WP_REST_Controller implements IHook
{
    use ResponseTrait;

    public function __construct()
    {
        $this->namespace = 'transcy-api';
    }

    /**
     * Register Hooks
     *
     * @since 4.4.0
     * @var array
     */
    public function registerHooks()
    {
        add_action('rest_api_init', array($this, 'registerRoutes'));

        add_action('rest_api_init', function () {

            remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');

            add_filter('rest_pre_serve_request', [$this, 'initCors']);
        }, 15);

        \add_filter('rest_allowed_cors_headers', function ($allow_headers) {

            $allow_headers[] = 'request-startTime';

            return $allow_headers;
        });
    }

    /**
     * Register Routes to WP_REST
     *
     * @since 4.4.0
     * @var array
     */
    public function registerRoutes()
    {
        foreach (glob(TRANSCY_PATH . '/routes/*.php') as $file) {
            include $file;
        }
        foreach (Route::getRoutes() as $route => $args) {
            $args['permission_callback'] = $this->permissionCallback($args['permission_callback']);
            $args['args'] = $this->getArgs($args['args']);

            register_rest_route($this->namespace, $route, $args);
        }
    }

    /**
     * Handle permission callback for route
     *
     * @since 4.4.0
     * @return bool
     */
    private function permissionCallback($permission)
    {
        if (!empty($permission)) {
            foreach ($permission as $value) {
                $middleware =  sprintf('\Illuminate\Middleware\%s', $value);
                if(class_exists($middleware)){
                    return [new $middleware(), 'handle'];
                }
                return [$this, 'middlewareNotExists'];
            }
        }

        return [$this, 'getItemsPermissionsCheck'];
    }


    /**
     * Handle args for route
     *
     * @since 4.4.0
     * @return bool
     */
    private function getArgs($args)
    {
        if (is_null($args)) {
            return $this->get_collection_params();
        }

        return $args;
    }

    /**
     * Permission check default
     *
     * @since 4.4.0
     * @return bool
     */
    public function middlewareNotExists($request)
    {
        $this->disableRestRespone();
        return $this->responseFailed('Class middleware invalid');
    }

    /**
     * Permission check default
     *
     * @since 4.4.0
     * @return bool
     */
    public function getItemsPermissionsCheck($request)
    {
        return true;
    }

    public function initCors($value)
    {
        Helper::corsHeaders();

        return $value;
    }
}
