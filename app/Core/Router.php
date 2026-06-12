<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routeur frontal léger.
 * Enregistre des routes GET/POST et dispatche vers Controller@méthode.
 */
class Router
{
    private array $routes = [];

    public function get(string $pattern, string $handler): void
    {
        $this->routes[] = ['GET', $pattern, $handler];
    }

    public function post(string $pattern, string $handler): void
    {
        $this->routes[] = ['POST', $pattern, $handler];
    }

    /**
     * Charge les fichiers de routes de chaque module.
     */
    public function loadModuleRoutes(): void
    {
        $modulesDir = BASE_PATH . '/app/Modules';
        foreach (glob($modulesDir . '/*/Routes/routes.php') as $file) {
            $router = $this;
            require $file;
        }
    }

    public function dispatch(string $method, string $uri): void
    {
        // Nettoyer l'URI : retirer le query string et le slash final
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== strtoupper($method)) continue;

            // Convertir le pattern en regex  :param → groupe nommé
            $regex = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controllerClass, $action] = explode('@', $handler);

                // Résoudre le FQCN si non qualifié
                if (!str_starts_with($controllerClass, 'App\\')) {
                    $controllerClass = 'App\\Modules\\' . $controllerClass;
                }

                if (!class_exists($controllerClass)) {
                    $this->abort(500, "Contrôleur introuvable : {$controllerClass}");
                    return;
                }

                $controller = new $controllerClass();

                if (!method_exists($controller, $action)) {
                    $this->abort(500, "Méthode introuvable : {$controllerClass}@{$action}");
                    return;
                }

                $controller->$action($params);
                return;
            }
        }

        $this->abort(404);
    }

    private function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $titles = [404 => 'Page introuvable', 403 => 'Accès refusé', 500 => 'Erreur serveur'];
        $title  = $titles[$code] ?? 'Erreur';
        include BASE_PATH . '/app/Shared/Views/error.php';
        exit;
    }
}
