<?php

namespace Illuminate\Traits;

use Illuminate\Configuration;
use Illuminate\Utils\Helper;

trait ResponseTrait
{
    protected $restResponse = true;

    /**
     * Set rest respone is false
     *
     * @return void
     */
    protected function disableRestRespone()
    {
        $this->restResponse = false;
    }

    /**
     * @param int $status
     * @param array $messages
     * @param array $datas
     *
     * @return \WP_REST_Response
     */
    protected function response($status = 1, $messages = [], $datas = [])
    {
        //ensure that the main query has been reset to the original main query
        wp_reset_postdata();
        
        ini_set( 'display_errors', 0 );

        $item = [
            'status'   => $status,
            'messages' => empty($messages) ? null : $messages,
            'data'     => $datas
        ];

        return  $this->restResponse
            ? new \WP_REST_Response($item, 200)
            : $this->sendJson($item);
    }

    /**
     * @param array|null $item
     *
     * @return \WP_REST_Response
     */
    private function sendJson($item)
    {
        Helper::corsHeaders();

        return wp_send_json($item);
    }

    /**
     * @param array|null $datas
     * @param array|string|null $message
     *
     * @return \WP_REST_Response
     */
    public function responseSuccess($datas = [], $messages = [])
    {
        return $this->response(
            Configuration::API_RESPONSE_STATUS_OK,
            $messages,
            $datas
        );
    }

    /**
     * @param array|string|null $messages
     * @param array|null $datas
     *
     * @return \WP_REST_Response
     */
    public function responseFailed($messages = [], $datas = null)
    {
        return $this->response(
            Configuration::API_RESPONSE_STATUS_ERROR,
            $messages,
            $datas
        );
    }


    /**
     * @param array|string|null $messages
     * @param array|null $datas
     *
     * @return \WP_REST_Response
     */
    public function responseFailedAuth($messages = [], $datas = null)
    {
        return $this->response(
            Configuration::API_RESPONSE_STATUS_ERROR_AUTH,
            $messages,
            $datas
        );
    }

    /**
     * @param array|null $datas
     * @param array|string|null $message
     *
     * @return \WP_REST_Response
     */
    public function responseRedirect($datas = [], $messages = [])
    {
        return $this->response(
            Configuration::API_RESPONSE_STATUS_REDIRECT,
            $messages,
            $datas
        );
    }
}
