<?php
$router->get('/archive',                        'Archive\Controllers\ArchiveController@index');
$router->get('/archive/:id',                    'Archive\Controllers\ArchiveController@show');
$router->post('/archive/:id/restaurer',         'Archive\Controllers\ArchiveController@restaurer');
$router->post('/archive/:id/supprimer',         'Archive\Controllers\ArchiveController@supprimerDefinitivement');
$router->get('/archive/:id/xml',                'Archive\Controllers\ArchiveController@telechargerXml');
