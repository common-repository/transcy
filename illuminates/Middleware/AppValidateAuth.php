<?php

namespace Illuminate\Middleware;

use Illuminate\Services\BaseService;
use Illuminate\Utils\Helper;

class AppValidateAuth extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * \WP_REST_Request $request
     */
    public function handle(\WP_REST_Request $request)
    {
        $header         = apache_request_headers();
        $header         = array_change_key_case($header);
        $authorization  = !empty($request->get_header('authorization')) ? $request->get_header('authorization') : $header['authorization'];

        if (empty($authorization)) {
            return $this->responseFailedAuth(__('Invalid header', 'transcy'));
        }

        $baseService = BaseService::getInstance();
        $decodeToken = $baseService->decodeToken(str_replace('Bearer ', '', $authorization));
        if (isset($decodeToken['status']) && $decodeToken['status']) {
            if ($decodeToken['domain'] !=  Helper::getDomain()) {
                return $this->responseFailedAuth(__('Permission denied', 'transcy'));
            }
            return true;
        } else {
            return $this->responseFailedAuth(isset($decodeToken['message']) ? $decodeToken['message'] : __('Permission denied', 'transcy'));
        }

        return true;
    }
}
