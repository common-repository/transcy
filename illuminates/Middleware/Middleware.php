<?php

namespace Illuminate\Middleware;

use Illuminate\Traits\ResponseTrait;

class Middleware
{
    use ResponseTrait;

    /**
     * Middleware constructor.
     *
     * @param ArticleRepository $articleRepository
     */
    public function __construct()
    {
        $this->disableRestRespone();
    }
}
