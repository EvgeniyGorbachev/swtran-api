<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {
    
});

//public API routes
$api->group(['middleware' => ['api', 'cors']], function ($api) {

    // Authentication Routes...
    $api->post('auth/login', 'Auth\AuthController@login');

    // Password Reset Routes...
    $api->post('auth/password/email', 'Auth\PasswordResetController@sendResetLinkEmail');
    $api->get('auth/password/verify', 'Auth\PasswordResetController@verify');
    $api->post('auth/password/reset', 'Auth\PasswordResetController@reset');
    
});

//protected API routes with JWT (must be logged in)
$api->group(['middleware' => ['api', 'api.auth', 'cors']], function ($api) {
    
    //user registration
    $api->post('user/register', 'Auth\AuthController@register');
    
    //get all users
    $api->get('user', 'UserController@get');
    
    //get all users roles
    $api->get('user/roles', 'UserController@getRoles');
    
    //get personal data
    $api->get('user/personal', 'UserController@getPersonal');
});


