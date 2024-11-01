<?php

namespace Illuminate\Exceptions;

use Illuminate\Utils\Helper;
use Illuminate\Traits\ResponseTrait;

class Handler extends \Exception
{
    use ResponseTrait;

    /**
     * Instance
     *
     * @return static
     */
    protected static $instance;

    /**
     * Get instance
     *
     * @return static
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new Handler();
        }
        return static::$instance;
    }

    /**
     * Register exception handler
     *
     * @return
     */
    public function register()
    {
        set_exception_handler(function ($exception) {

            return $this->render($exception);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @return
     */
    public function render($exception)
    {
        if (!Helper::isSpineApi()) {
            return;
        }

        //set false to make response json and exist
        $this->disableRestRespone();

        return $this->responseFailed(
            $this->showMessage($exception)
                ? $exception->getMessage()
                : __('Something went wrong'),
            $this->showMessage($exception)
                ? ['_trace' => $exception->getTrace()]
                : [],

        );
    }

    private function showMessage($exception)
    {
        return Helper::isProduction() && !$exception instanceof AppException
            ? false
            : true;
    }
}
