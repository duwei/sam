<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => ['throttle:30,1']], function () use ($router) {
    $router->get('/callback', 'ApiController@callback');

    $router->get('/me', 'ApiController@me');
    $router->get('/success', 'ApiController@success');
    $router->post('/refresh', 'ApiController@refresh');

    $router->get('/services', 'ServiceController@index');
    $router->post('/service/register', 'ServiceController@create');
    $router->post('/service/update', 'ServiceController@update');
    $router->post('/service/delete', 'ServiceController@delete');

});

$router->get('login', function () use ($router) {
    $query = http_build_query([
        'client_id' => '5',
        'redirect_uri' => 'http://211.110.209.62:9191/api/callback',
        'response_type' => 'code',
        'state' => 'service_id=1',
        'scope' => '*',
    ]);
    return redirect('http://211.110.209.62:8081/api/oauth/authorize?' . $query);
});

$router->get('google_login', function () use ($router) {
    $query = http_build_query([
        'client_id' => '272393273254-8tvks1u0o0m64er3t04kf2tk9lbda366.apps.googleusercontent.com',
//        'redirect_uri' => 'https://tessverso.io/sam/api/callback',
        'redirect_uri' => 'http://localhost:9090/api/callback',
        'response_type' => 'code',
        'state' => 'service_id=2',
        'access_type' => 'offline',
        'scope' => 'openid profile email',
    ]);
    return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
});
