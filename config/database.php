<?php

/**
 * Configuration de la base de données PostgreSQL.
 * Adapter selon l'environnement.
 */
return [
    'host'     => $_ENV['DB_HOST']     ?? $_ENV['PGHOST']     ?? 'localhost',
    'port'     => $_ENV['DB_PORT']     ?? $_ENV['PGPORT']     ?? '5432',
    'dbname'   => $_ENV['DB_NAME']     ?? $_ENV['PGDATABASE'] ?? 'gestion_stock',
    'user'     => $_ENV['DB_USER']     ?? $_ENV['PGUSER']     ?? 'postgres',
    'password' => $_ENV['DB_PASSWORD'] ?? $_ENV['PGPASSWORD'] ?? '',
];
