<?php
// ── Commandes fournisseurs ───────────────────────────────────────────────
$router->get('/approvisionnement/commandes',                  'Approvisionnement\Controllers\CommandeFournisseurController@index');
$router->get('/approvisionnement/commandes/creer',            'Approvisionnement\Controllers\CommandeFournisseurController@creerForm');
$router->post('/approvisionnement/commandes/creer',           'Approvisionnement\Controllers\CommandeFournisseurController@creerTraiter');
$router->get('/approvisionnement/commandes/:id/edit',         'Approvisionnement\Controllers\CommandeFournisseurController@editForm');
$router->post('/approvisionnement/commandes/:id/edit',        'Approvisionnement\Controllers\CommandeFournisseurController@editTraiter');
$router->post('/approvisionnement/commandes/:id/valider',     'Approvisionnement\Controllers\CommandeFournisseurController@valider');
$router->post('/approvisionnement/commandes/:id/annuler',     'Approvisionnement\Controllers\CommandeFournisseurController@annuler');
$router->post('/approvisionnement/commandes/:id/supprimer',   'Approvisionnement\Controllers\CommandeFournisseurController@supprimer');
$router->get('/approvisionnement/commandes/:id/recevoir',     'Approvisionnement\Controllers\ReceptionController@creerForm');
$router->post('/approvisionnement/commandes/:id/recevoir',    'Approvisionnement\Controllers\ReceptionController@creerTraiter');
$router->get('/approvisionnement/commandes/:id',              'Approvisionnement\Controllers\CommandeFournisseurController@show');

// ── Réceptions ────────────────────────────────────────────────────────────
$router->get('/approvisionnement/receptions',                 'Approvisionnement\Controllers\ReceptionController@index');
$router->post('/approvisionnement/receptions/:id/supprimer',  'Approvisionnement\Controllers\ReceptionController@supprimer');
$router->get('/approvisionnement/receptions/:id',              'Approvisionnement\Controllers\ReceptionController@show');

// ── Factures fournisseurs ────────────────────────────────────────────────
$router->get('/approvisionnement/factures',                   'Approvisionnement\Controllers\FactureFournisseurController@index');
$router->get('/approvisionnement/factures/creer',             'Approvisionnement\Controllers\FactureFournisseurController@creerForm');
$router->post('/approvisionnement/factures/creer',            'Approvisionnement\Controllers\FactureFournisseurController@creerTraiter');
$router->post('/approvisionnement/factures/:id/reglement',    'Approvisionnement\Controllers\FactureFournisseurController@reglementTraiter');
$router->post('/approvisionnement/factures/:id/supprimer',    'Approvisionnement\Controllers\FactureFournisseurController@supprimer');
$router->get('/approvisionnement/factures/:id',                'Approvisionnement\Controllers\FactureFournisseurController@show');

// ── Dons ──────────────────────────────────────────────────────────────────
$router->get('/approvisionnement/dons',                       'Approvisionnement\Controllers\DonController@index');
$router->get('/approvisionnement/dons/creer',                 'Approvisionnement\Controllers\DonController@creerForm');
$router->post('/approvisionnement/dons/creer',                'Approvisionnement\Controllers\DonController@creerTraiter');
$router->post('/approvisionnement/dons/:id/supprimer',        'Approvisionnement\Controllers\DonController@supprimer');
$router->get('/approvisionnement/dons/:id',                    'Approvisionnement\Controllers\DonController@show');

// ── Rapports & Éditions ──────────────────────────────────────────────────
$router->get('/approvisionnement/rapports',                       'Approvisionnement\Controllers\EditionController@rapports');
$router->get('/approvisionnement/rapports/achats-jour',           'Approvisionnement\Controllers\EditionController@etatAchatsJour');
$router->get('/approvisionnement/rapports/achats-annuels',        'Approvisionnement\Controllers\EditionController@achatsAnnuels');
$router->get('/approvisionnement/commandes/:id/imprimer',         'Approvisionnement\Controllers\EditionController@bonCommande');
$router->get('/approvisionnement/receptions/:id/imprimer',        'Approvisionnement\Controllers\EditionController@bonReception');
$router->get('/approvisionnement/factures/:id/imprimer',          'Approvisionnement\Controllers\EditionController@facture');
$router->get('/approvisionnement/reglements/:id/recu',            'Approvisionnement\Controllers\EditionController@recu');
$router->get('/approvisionnement/dons/:id/imprimer',              'Approvisionnement\Controllers\EditionController@bonEntree');
