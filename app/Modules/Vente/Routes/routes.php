<?php
// ── Commandes clients ────────────────────────────────────────────────────
$router->get('/vente/commandes',                  'Vente\Controllers\CommandeClientController@index');
$router->get('/vente/commandes/creer',             'Vente\Controllers\CommandeClientController@creerForm');
$router->post('/vente/commandes/creer',            'Vente\Controllers\CommandeClientController@creerTraiter');
$router->get('/vente/commandes/:id/edit',          'Vente\Controllers\CommandeClientController@editForm');
$router->post('/vente/commandes/:id/edit',         'Vente\Controllers\CommandeClientController@editTraiter');
$router->post('/vente/commandes/:id/valider',      'Vente\Controllers\CommandeClientController@valider');
$router->post('/vente/commandes/:id/annuler',      'Vente\Controllers\CommandeClientController@annuler');
$router->post('/vente/commandes/:id/supprimer',    'Vente\Controllers\CommandeClientController@supprimer');
$router->get('/vente/commandes/:id/livrer',        'Vente\Controllers\LivraisonController@creerForm');
$router->post('/vente/commandes/:id/livrer',       'Vente\Controllers\LivraisonController@creerTraiter');
$router->get('/vente/commandes/:id',               'Vente\Controllers\CommandeClientController@show');

// ── Vente comptant ────────────────────────────────────────────────────────
$router->get('/vente/comptant',                    'Vente\Controllers\VenteComptantController@index');
$router->get('/vente/comptant/creer',               'Vente\Controllers\VenteComptantController@creerForm');
$router->post('/vente/comptant/creer',              'Vente\Controllers\VenteComptantController@creerTraiter');

// ── Livraisons ────────────────────────────────────────────────────────────
$router->get('/vente/livraisons',                  'Vente\Controllers\LivraisonController@index');
$router->post('/vente/livraisons/:id/supprimer',   'Vente\Controllers\LivraisonController@supprimer');
$router->get('/vente/livraisons/:id',               'Vente\Controllers\LivraisonController@show');

// ── Factures clients ──────────────────────────────────────────────────────
$router->get('/vente/factures',                    'Vente\Controllers\FactureClientController@index');
$router->get('/vente/factures/creer',               'Vente\Controllers\FactureClientController@creerForm');
$router->post('/vente/factures/creer',              'Vente\Controllers\FactureClientController@creerTraiter');
$router->post('/vente/factures/:id/reglement',      'Vente\Controllers\FactureClientController@reglementTraiter');
$router->post('/vente/factures/:id/supprimer',      'Vente\Controllers\FactureClientController@supprimer');
$router->get('/vente/factures/:id',                  'Vente\Controllers\FactureClientController@show');

// ── Bons de sortie ────────────────────────────────────────────────────────
$router->get('/vente/sorties',                      'Vente\Controllers\SortieController@index');
$router->get('/vente/sorties/creer',                 'Vente\Controllers\SortieController@creerForm');
$router->post('/vente/sorties/creer',                'Vente\Controllers\SortieController@creerTraiter');
$router->post('/vente/sorties/:id/supprimer',        'Vente\Controllers\SortieController@supprimer');
$router->get('/vente/sorties/:id',                    'Vente\Controllers\SortieController@show');

// ── Rapports & Éditions Vente ─────────────────────────────────────────────
$router->get('/vente/rapports',                          'Vente\Controllers\EditionVenteController@rapports');
$router->get('/vente/rapports/ventes-jour',              'Vente\Controllers\EditionVenteController@etatVentesJour');
$router->get('/vente/rapports/ventes-annuelles',         'Vente\Controllers\EditionVenteController@ventesAnnuelles');
$router->get('/vente/commandes/:id/imprimer',            'Vente\Controllers\EditionVenteController@bonCommande');
$router->get('/vente/livraisons/:id/imprimer',           'Vente\Controllers\EditionVenteController@bonLivraison');
$router->get('/vente/factures/:id/imprimer',             'Vente\Controllers\EditionVenteController@facture');
$router->get('/vente/reglements/:id/recu',               'Vente\Controllers\EditionVenteController@recu');
$router->get('/vente/sorties/:id/imprimer',              'Vente\Controllers\EditionVenteController@bonSortie');
