<?php
$router->get('/audit',          'Audit\Controllers\AuditController@index');
$router->get('/audit/export',   'Audit\Controllers\AuditController@export');
$router->post('/audit/purger',  'Audit\Controllers\AuditController@purger');
$router->get('/audit/:id',      'Audit\Controllers\AuditController@show');
