<?php

namespace TranscyAdmin\Controllers;

use Illuminate\BaseClass\Controller;
use TranscyAdmin\Repositories\BaseApiRepositores;
use Illuminate\Services\AppService;
use Illuminate\Utils\Helper;

class BaseApiControlor extends Controller
{
    protected $baseApiRepositores;

    /**
     * ArticleController constructor.
     *
     * @param BaseApiRepositores $baseApiRepositores
     */
    public function __construct()
    {
        $this->baseApiRepositores = new BaseApiRepositores();
    }

    /**
     * Get token site
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getToken(\WP_REST_Request $request)
    {
        return $this->responseSuccess($this->baseApiRepositores->getToken());
    }

    /**
     * Register with app
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function register(\WP_REST_Request $request)
    {
        //Tracking to app active
        $appService = AppService::getInstance();
        $appService->trackingRegister();

        if (!Helper::isActiveOnApp()) {
            return $this->responseFailed(__('Register faile'));
        }

        return $this->responseSuccess($this->baseApiRepositores->getToken());
    }
}
