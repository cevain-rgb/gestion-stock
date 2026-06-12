<?php

/**
 * Configuration générale de l'application.
 */
return [
    'name'     => 'StockManager',
    'env'      => $_ENV['APP_ENV'] ?? 'development',
    'debug'    => ($_ENV['APP_DEBUG'] ?? 'true') === 'true',
    'base_url' => $_ENV['APP_URL']  ?? 'http://localhost/gestion-stock/public',
    'timezone' => 'Africa/Douala',
    'locale'   => 'fr_FR',
];
