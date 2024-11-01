<?php

use Illuminate\Route\Route;

//set default namespace
Route::setNamespace('\\TranscyAdmin\\Controllers\\');

Route::group(['middleware' => ['BaseValidateAuth']], function () {
    //Get list resource
    Route::get('token', [BaseApiControlor::class, 'getToken']);

    //Register with app
    Route::post('register', [BaseApiControlor::class, 'register']);
});
