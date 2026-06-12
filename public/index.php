<?php

declare(strict_types=1);

//  Constantes fondamentales 
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', $_ENV['APP_URL'] ?? 'http://localhost/gestion-stock/public'); // override via .env

//  Timezone & encodage 
date_default_timezone_set('Africa/Douala');
mb_internal_encoding('UTF-8');

//  Chargement du .env (optionnel, simple parser maison) 
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $val] = explode('=', $line, 2) + [1 => ''];
        $_ENV[trim($key)] = trim($val);
    }
}

//  Autoloader PSR-4 
require BASE_PATH . '/app/Core/Autoloader.php';

//  Helpers globaux 
require BASE_PATH . '/app/Shared/Helpers/helpers.php';

//  Gestion des erreurs 
$appConfig = require BASE_PATH . '/config/app.php';
if ($appConfig['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

//  Session sécurisée 
\App\Core\Session::start();

//  En-têtes de sécurité 
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

//  Routeur 
$router = new \App\Core\Router();
$router->loadModuleRoutes();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// Retirer le préfixe du sous-dossier si l'appli est dans un sous-répertoire
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir));
}
$uri = $uri ?: '/';

$router->dispatch($method, $uri);
