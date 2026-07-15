<?php

/**
 * Configuration de la base de données PostgreSQL.
 * Adapter selon l'environnement.
 */
return [
    'host'     => $_ENV['PGHOST']     ?? 'localhost',
    'port'     => $_ENV['PGPORT']     ?? '5432',
    'dbname'   => $_ENV['PGDATABASE']     ?? 'gestion_stock',
    'user'     => $_ENV['PGUSER']     ?? 'postgres',
    'password' => $_ENV['PGPASSWORD'] ?? '',
];
