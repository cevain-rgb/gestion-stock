<?php
/** Routes Auth */
$router->get('/login',  'Auth\Controllers\AuthController@loginForm');
$router->post('/login', 'Auth\Controllers\AuthController@loginTraiter');
$router->post('/logout','Auth\Controllers\AuthController@logout');
$router->get('/', 'Auth\Controllers\AuthController@loginForm');
