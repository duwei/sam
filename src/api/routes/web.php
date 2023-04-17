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

    $router->post('/tess/login', 'ApiController@login');
    $router->get('/me', 'ApiController@me');
    $router->get('/success', 'ApiController@success');
    $router->get('/token/verify', 'ApiController@verify');
    $router->post('/token/refresh', 'ApiController@refresh');
    $router->get('/token/invalidate', 'ApiController@logout');

    $router->get('/services', 'ServiceController@index');
    $router->post('/service/register', 'ServiceController@create');
    $router->post('/service/update', 'ServiceController@update');
    $router->post('/service/delete', 'ServiceController@delete');

});

$router->get('login', function () use ($router) {
    $query = http_build_query([
        'client_id' => '98eb7a8c-636f-4bb1-8108-f7cf38af09cb',
        'redirect_uri' => 'http://172.20.20.198:9090/api/callback',
//        'client_id' => '6',
//        'redirect_uri' => 'http://localhost:9090/api/callback',
        'response_type' => 'code',
        'state' => 'service_id=SrvhHFW3BJj',
        'scope' => '*',
    ]);
    return redirect('http://211.110.209.62:7070/api/oauth/authorize?' . $query);
});

$router->get('login2', function () use ($router) {
    $query = http_build_query([
        'client_id' => '98ecfaf0-4260-4465-853b-f0be98313676',
        'redirect_uri' => 'http://172.20.20.198:9090/api/callback',
        'response_type' => 'code',
        'state' => 'service_id=Srvrr9ENY7f',
        'scope' => '*',
    ]);
    return redirect('http://211.110.209.62:7070/api/oauth/authorize?' . $query);
});

$router->get('google_login', function () use ($router) {
    $query = http_build_query([
        'client_id' => '1092030563161-5s3v1757qlvsupikp1oj72vc7l1v2152.apps.googleusercontent.com',
        'redirect_uri' => 'https://tessverso.io/api/redirect.php',
        'response_type' => 'code',
        'state' => 'service_id=SrvYLUkJpnh',
        'access_type' => 'offline',
        'scope' => 'openid profile email',
    ]);
    return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
});

$router->get('kakao_login', function () use ($router) {
    $query = http_build_query([
        'client_id' => 'd73cb37e768ed401e970463738f80d0d',
        'redirect_uri' => 'http://172.20.20.198:9090/api/callback',
        'response_type' => 'code',
        'state' => 'service_id=SrvhcvSewh6',
    ]);
    return redirect('https://kauth.kakao.com/oauth/authorize?' . $query);
});
