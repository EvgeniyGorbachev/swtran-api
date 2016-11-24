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

    $api->group(['middleware' => ['role:admin|manager|support']], function ($api) {
        //get all users
        $api->get('user', 'UserController@get');
    });
    
    $api->group(['middleware' => ['role:admin']], function ($api) {
        //get all users
        $api->delete('user/{user}', 'UserController@deleteUser');
    });

    $api->group(['middleware' => ['role:admin|manager']], function ($api) {
        
        //user registration
        $api->post('user/register', 'Auth\AuthController@register');
        
        //user by id
        $api->get('user/{user}', 'UserController@getUser');    
        
        //edit user
        $api->put('user', 'UserController@editUser');
        
        //user registration
        $api->post('user/upload', 'UserController@upload');
        
        //create new role
        $api->get('user/entrust', 'UserController@createEntrustEntity');
    });
    
    //get all users roles
    $api->get('user/info/roles', 'UserController@getRoles');
    
    //login check for uniqueness
    $api->post('user/check-login', 'UserController@checkLogin');
    
    //get personal data
    $api->get('user/info/personal', 'UserController@getPersonal');
});


