<?php

namespace TranscyAdmin\Repositories;

use Illuminate\BaseClass\Repository;
use Illuminate\Services\BaseService;
use Illuminate\Configuration;

class BaseApiRepositores extends Repository
{
    /**
     * Get Token
     *
     * @return array
     */
    public function getToken(){
        $baseService = BaseService::getInstance();
        return [
            'expired_time' => Configuration::EXPIRATION_TIME,
            'token'        => $baseService->generateToken()
        ];
    }
}
