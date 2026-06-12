<?php

/**
 * Configuration de la base de données PostgreSQL.
 * Adapter selon l'environnement.
 */
return [
    'host'     => $_ENV['DB_HOST']     ?? 'localhost',
    'port'     => $_ENV['DB_PORT']     ?? '5432',
    'dbname'   => $_ENV['DB_NAME']     ?? 'gestion_stock',
    'user'     => $_ENV['DB_USER']     ?? 'postgres',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
];
