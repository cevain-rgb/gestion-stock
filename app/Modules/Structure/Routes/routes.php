<?php
// ── Familles ──────────────────────────────────────────────────────────────
$router->get('/structure/familles',              'Structure\Controllers\FamilleController@index');
$router->get('/structure/familles/creer',        'Structure\Controllers\FamilleController@creerForm');
$router->post('/structure/familles/creer',       'Structure\Controllers\FamilleController@creerTraiter');
$router->get('/structure/familles/:id/edit',     'Structure\Controllers\FamilleController@editForm');
$router->post('/structure/familles/:id/edit',    'Structure\Controllers\FamilleController@editTraiter');
$router->post('/structure/familles/:id/supprimer','Structure\Controllers\FamilleController@supprimer');

// ── Produits ──────────────────────────────────────────────────────────────
$router->get('/structure/produits',              'Structure\Controllers\ProduitController@index');
$router->get('/structure/produits/creer',        'Structure\Controllers\ProduitController@creerForm');
$router->post('/structure/produits/creer',       'Structure\Controllers\ProduitController@creerTraiter');
$router->get('/structure/produits/:id/edit',     'Structure\Controllers\ProduitController@editForm');
$router->post('/structure/produits/:id/edit',    'Structure\Controllers\ProduitController@editTraiter');
$router->post('/structure/produits/:id/supprimer','Structure\Controllers\ProduitController@supprimer');
$router->get('/structure/produits/:id',          'Structure\Controllers\ProduitController@show');

// ── Fournisseurs ──────────────────────────────────────────────────────────
$router->get('/structure/fournisseurs',              'Structure\Controllers\FournisseurController@index');
$router->get('/structure/fournisseurs/creer',        'Structure\Controllers\FournisseurController@creerForm');
$router->post('/structure/fournisseurs/creer',       'Structure\Controllers\FournisseurController@creerTraiter');
$router->get('/structure/fournisseurs/:id/edit',     'Structure\Controllers\FournisseurController@editForm');
$router->post('/structure/fournisseurs/:id/edit',    'Structure\Controllers\FournisseurController@editTraiter');
$router->post('/structure/fournisseurs/:id/supprimer','Structure\Controllers\FournisseurController@supprimer');
$router->get('/structure/fournisseurs/:id',          'Structure\Controllers\FournisseurController@show');

// ── Clients ───────────────────────────────────────────────────────────────
$router->get('/structure/clients',               'Structure\Controllers\ClientController@index');
$router->get('/structure/clients/creer',         'Structure\Controllers\ClientController@creerForm');
$router->post('/structure/clients/creer',        'Structure\Controllers\ClientController@creerTraiter');
$router->get('/structure/clients/:id/edit',      'Structure\Controllers\ClientController@editForm');
$router->post('/structure/clients/:id/edit',     'Structure\Controllers\ClientController@editTraiter');
$router->post('/structure/clients/:id/supprimer','Structure\Controllers\ClientController@supprimer');
$router->get('/structure/clients/:id',           'Structure\Controllers\ClientController@show');

// ── Catégories clients ────────────────────────────────────────────────────
$router->get('/structure/categories',               'Structure\Controllers\CategorieController@index');
$router->get('/structure/categories/creer',         'Structure\Controllers\CategorieController@creerForm');
$router->post('/structure/categories/creer',        'Structure\Controllers\CategorieController@creerTraiter');
$router->get('/structure/categories/:id/edit',      'Structure\Controllers\CategorieController@editForm');
$router->post('/structure/categories/:id/edit',     'Structure\Controllers\CategorieController@editTraiter');
$router->post('/structure/categories/:id/supprimer','Structure\Controllers\CategorieController@supprimer');

// ── Banques ───────────────────────────────────────────────────────────────
$router->get('/structure/banques',                'Structure\Controllers\BanqueController@index');
$router->post('/structure/banques/creer',         'Structure\Controllers\BanqueController@creerTraiter');
$router->get('/structure/banques/:id/edit',       'Structure\Controllers\BanqueController@editForm');
$router->post('/structure/banques/:id/edit',      'Structure\Controllers\BanqueController@editTraiter');
$router->post('/structure/banques/:id/versement', 'Structure\Controllers\BanqueController@versement');
$router->post('/structure/banques/:id/supprimer', 'Structure\Controllers\BanqueController@supprimer');
$router->get('/structure/banques/:id',            'Structure\Controllers\BanqueController@show');

// ── Éditions & Rapports Structure ────────────────────────────────────────
$router->get('/structure/rapports',                        'Structure\Controllers\EditionStructureController@index');
$router->get('/structure/rapports/produits-familles',      'Structure\Controllers\EditionStructureController@listeProduitsFamilles');
$router->get('/structure/familles/:id/produits',           'Structure\Controllers\EditionStructureController@produitsFamille');
$router->get('/structure/rapports/fournisseurs',           'Structure\Controllers\EditionStructureController@listeFournisseurs');
$router->get('/structure/rapports/clients',                'Structure\Controllers\EditionStructureController@listeClients');
$router->get('/structure/rapports/banques',                'Structure\Controllers\EditionStructureController@listeBanques');
$router->get('/structure/rapports/versements',             'Structure\Controllers\EditionStructureController@versementsBanque');
