<?php

declare(strict_types=1);

/**
 * PSR-4 autoloader maison.
 * Namespace racine : App  →  BASE_PATH/app
 */
spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) return;

    $relative = str_replace(['App\\', '\\'], ['', '/'], $class);
    $file = BASE_PATH . '/app/' . $relative . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
