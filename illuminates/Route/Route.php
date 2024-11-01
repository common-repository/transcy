<?php

namespace Illuminate\Route;

class Route
{
    public static $prefix = '';

    public static $namespace = '';

    public static $middleware = null;

    public static $routes = [];

    /**
     * Set prefix for route
     *
     * @param string $prefix
     *
     * @since 4.4.0
     * @var array
     */
    protected static function setPrefix(string $prefix = '')
    {
        self::$prefix = empty($prefix) ? '' : sprintf('%s/', $prefix);
    }

    /**
     * Set prefix for route
     *
     * @param string $prefix
     *
     * @since 4.4.0
     * @var array
     */
    protected static function setMiddleware(array $middleware = null)
    {
        self::$middleware = empty($middleware) ? null : $middleware;
    }

    /**
     * Set default namespace
     *
     * @param string $prefix
     *
     * @since 4.4.0
     * @var array
     */
    public static function setNamespace(string $namespace = '')
    {
        self::$namespace = $namespace;
    }

    /**
     * Set route to array.
     *
     * @param string $method
     * @param string $route
     * @param array $callback
     * @param array $permission
     * @param array $args
     *
     * @since 4.4.0
     * @var array
     */
    public static function setRoute(string $method, string $route, array $callback, array $permission = null, $args = null)
    {
        //get current class
        $class = sprintf('%s%s', self::$namespace, $callback[0]);

        $permissionTemp = [];
        if(!empty($permission)){
            $permissionTemp = $permission;
        }
        if(!empty(self::$middleware)){
            $permissionTemp = self::$middleware;
        }
        if(!empty($permission) && !empty(self::$middleware)){
            $permissionTemp = array_merge($permission, self::$middleware);
        }

        self::$routes[sprintf('%s%s', self::$prefix, $route)] = [
            'methods'  => $method,
            'callback' => [$class::getInstance(), $callback[1]],
            'args'     => $args,
            'permission_callback' => empty($permissionTemp) ? null : $permissionTemp
        ];
    }

    /**
     * Retrieves all routes.
     *
     * @since 4.4.0
     * @var array
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Make route is get method
     *
     * @param string $route
     * @param array $callback
     * @param array $permission
     * @param array $args
     *
     * @since 4.4.0
     * @var array
     */
    public static function get(string $route, array $callback, array $permission = null, $args = null)
    {
        self::setRoute(\WP_REST_Server::READABLE, $route, $callback, $permission, $args);
    }

    /**
     * Make route is post method
     *
     * @param string $route
     * @param array $callback
     * @param array $permission
     * @param array $args
     *
     * @since 4.4.0
     * @var array
     */
    public static function post(string $route, array $callback, array $permission = null, $args = null)
    {
        self::setRoute(\WP_REST_Server::CREATABLE, $route, $callback, $permission, $args);
    }

        /**
     * Make route is delete method
     *
     * @param string $route
     * @param array $callback
     * @param array $permission
     * @param array $args
     *
     * @since 4.4.0
     * @var array
     */
    public static function delete(string $route, array $callback, array $permission = null, $args = null)
    {
        self::setRoute(\WP_REST_Server::DELETABLE, $route, $callback, $permission, $args);
    }

    /**
     * Group route item
     *
     * @param array $args
     * @param object $callback
     *
     * @since 4.4.0
     * @var array
     */
    public static function group(array $args, $callback)
    {
        //set prefix follow by group
        self::setPrefix(($args['prefix'] ?? ''));

        //set middleware
        self::setMiddleware(($args['middleware'] ?? null));

        //call callback
        $callback();

        //reset prefix to default
        self::setPrefix();

        //reset middleware default
        self::setMiddleware();
    }
}
