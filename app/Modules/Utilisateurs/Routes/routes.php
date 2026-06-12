<?php
//  Groupes ─
$router->get('/utilisateurs/groupes',                       'Utilisateurs\Controllers\GroupeController@index');
$router->get('/utilisateurs/groupes/creer',                 'Utilisateurs\Controllers\GroupeController@creerForm');
$router->post('/utilisateurs/groupes/creer',                'Utilisateurs\Controllers\GroupeController@creerTraiter');
$router->get('/utilisateurs/groupes/:id/edit',              'Utilisateurs\Controllers\GroupeController@editForm');
$router->post('/utilisateurs/groupes/:id/edit',             'Utilisateurs\Controllers\GroupeController@editTraiter');
$router->post('/utilisateurs/groupes/:id/supprimer',        'Utilisateurs\Controllers\GroupeController@supprimer');
$router->get('/utilisateurs/groupes/:id/droits',            'Utilisateurs\Controllers\GroupeController@droitsForm');
$router->post('/utilisateurs/groupes/:id/droits',           'Utilisateurs\Controllers\GroupeController@droitsTraiter');

//  Comptes 
$router->get('/utilisateurs/comptes',                       'Utilisateurs\Controllers\CompteController@index');
$router->get('/utilisateurs/comptes/creer',                 'Utilisateurs\Controllers\CompteController@creerForm');
$router->post('/utilisateurs/comptes/creer',                'Utilisateurs\Controllers\CompteController@creerTraiter');
$router->get('/utilisateurs/comptes/:id',                   'Utilisateurs\Controllers\CompteController@show');
$router->get('/utilisateurs/comptes/:id/edit',              'Utilisateurs\Controllers\CompteController@editForm');
$router->post('/utilisateurs/comptes/:id/edit',             'Utilisateurs\Controllers\CompteController@editTraiter');
$router->post('/utilisateurs/comptes/:id/actif',            'Utilisateurs\Controllers\CompteController@basculerActif');
$router->post('/utilisateurs/comptes/:id/mdp',              'Utilisateurs\Controllers\CompteController@changerMdp');
$router->post('/utilisateurs/comptes/:id/supprimer',        'Utilisateurs\Controllers\CompteController@supprimer');

//  Profil connecté 
$router->get('/profil',      'Utilisateurs\Controllers\ProfilController@index');
$router->post('/profil/mdp', 'Utilisateurs\Controllers\ProfilController@changerMdp');
