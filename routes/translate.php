<?php

use Illuminate\Route\Route;

//set default namespace
Route::setNamespace('\\TranscyApp\\Controllers\\');

Route::group(['middleware' => ['AppValidateAuth']], function () {
    
    //Resource type post
    Route::get('resources/get/(?P<type>[\S]+)', [ResourcePostController::class, 'index']);
    Route::post('resources/translate/(?P<type>[\S]+)/(?P<id>[0-9_-]+)', [ResourcePostController::class, 'translate']);
    Route::post('resources/delete/(?P<type>[\S]+)/(?P<id>[0-9_-]+)', [ResourcePostController::class, 'delete']);
    Route::get('resources/detail/(?P<type>[\S]+)/(?P<id>[0-9_-]+)', [ResourcePostController::class, 'detail']);

    //Resource type term
    Route::get('term/get/(?P<type>[\S]+)', [ResourceTermController::class, 'index']);
    Route::post('term/translate/(?P<type>[\S]+)/(?P<id>[0-9_-]+)', [ResourceTermController::class, 'translate']);
    Route::post('term/delete/(?P<type>[\S]+)/(?P<id>[0-9_-]+)', [ResourceTermController::class, 'delete']);
    Route::get('term/detail/(?P<type>[\S]+)/(?P<id>[0-9_-]+)', [ResourceTermController::class, 'detail']);

    //Resource Menu
    Route::get('menu/get', [MenuController::class, 'get']);
    Route::get('menu/item/get/(?P<id>[0-9_-]+)', [MenuController::class, 'getItem']);
    Route::post('menu/item/translate/(?P<id>[0-9_-]+)', [MenuController::class, 'translateItem']);
    Route::post('menu/item/delete/(?P<id>[0-9_-]+)', [MenuController::class, 'deleteItem']);
    Route::get('menu/locations', [MenuController::class, 'locations']);
});
