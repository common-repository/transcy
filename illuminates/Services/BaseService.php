<?php

namespace Illuminate\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Utils\Helper;
use Illuminate\Configuration;
use Illuminate\Traits\MemorySingletonTrait;

class BaseService
{
    use MemorySingletonTrait;
    /**
     * Endpoint API services.
     * @param string
     */
    protected string $endpoint;

    /**
     * Generate token
     *
     * @return string
     */
    public function generateToken()
    {
        $payload = [
            'domain'    => Helper::getDomain(),
            'email'     => get_option('admin_email'),
            'exp'       => time() + Configuration::EXPIRATION_TIME,
            'iss'       => 'transcy',
            'aud'       => md5('wordpress'),
            'iat'       => time()
        ];
        $token = JWT::encode($payload, $this->getAppKey(), 'HS256');

        return $token;
    }

    /**
     * Decode token
     *
     * @return string
     */
    public function decodeToken(string $token = '')
    {
        $message = __('An error, please try again.', 'transcy');
        try {
            $data = JWT::decode($token, new Key($this->getAppKey(), 'HS256'));
            return [
                'status' => true,
                'domain' => $data->domain
            ];
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }

        return [
            'status'     => false,
            'message'    => $message
        ];
    }

    public function generateKey()
    {
        // $responseBody   = $this->get('api/auth/key');
        // if (isset($responseBody->data->key) && !empty($responseBody->data->key)) {
        //     return $responseBody->data->key;
        // }
        // return null;
    }

    public function getKey()
    {
        $key = get_option('transcy_apikey');
        if (empty($key)) {
            return null;
        }
        return $key;
    }

    public function getAppKey()
    {
        $key = get_option('transcy_app_apikey');
        if (empty($key)) {
            return null;
        }
        return $key;
    }

    public function get(string $path, array $param = [])
    {
        try {
            $apiUrl   = sprintf('%s/%s', $this->endpoint, $path);
            $args     = array(
                'headers' => array(
                    'Accept' => 'application/json',
                )
            );
            $args['headers'] = array_merge($args['headers'], $param);
            $response        = wp_remote_get($apiUrl, $args);
            if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
                return json_decode($response['body']);
            }
            return null;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function post(string $path, array $param = [], array $body = [])
    {
        try {
            $apiUrl   = sprintf('%s/%s', $this->endpoint, $path);
            $args     = array(
                'headers' => array(
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                )
            );
            if (!empty($param)) {
                $args['headers']    = array_merge($args['headers'], $param);
            }

            if (!empty($body)) {
                $args['body']       = wp_json_encode($body);
            }

            $response        = wp_remote_post($apiUrl, $args);
            if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
                return json_decode($response['body']);
            }
            return null;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function put(string $path, array $param = [], array $body = [])
    {
        try {
            $apiUrl   = sprintf('%s/%s', $this->endpoint, $path);
            $args     = array(
                'method'  => 'PUT',
                'headers' => array(
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                )
            );
            if (!empty($param)) {
                $args['headers']    = array_merge($args['headers'], $param);
            }

            if (!empty($body)) {
                $args['body']       = wp_json_encode($body);
            }

            $response        = wp_remote_request($apiUrl, $args);
            if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
                return json_decode($response['body']);
            }
            return null;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
