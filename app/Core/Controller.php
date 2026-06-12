<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Contrôleur de base.
 * Fournit rendu de vue, redirections, accès session et vérification CSRF.
 */
abstract class Controller
{
    // ------------------------------------------------------------------ Vue

    /**
     * Rend une vue du module courant.
     * $view : chemin relatif depuis app/Modules/{Module}/Views/ ou app/Shared/Views/
     * $data : variables injectées dans la vue
     */
    protected function render(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);

        // Rendre les données accessibles comme variables dans la vue
        extract($data, EXTR_SKIP);

        // Résolution du chemin : 'Auth/login' → app/Modules/Auth/Views/login.php
        //                        'shared/layout' → app/Shared/Views/layout.php
        if (str_starts_with($view, 'shared/')) {
            $path = BASE_PATH . '/app/Shared/Views/' . substr($view, 7) . '.php';
        } else {
            [$module, $tpl] = explode('/', $view, 2);
            $path = BASE_PATH . '/app/Modules/' . $module . '/Views/' . $tpl . '.php';
        }

        if (!file_exists($path)) {
            throw new \RuntimeException("Vue introuvable : {$path}");
        }

        include $path;
    }

    /**
     * Rend une vue dans le layout principal (sidebar + header).
     */
    protected function renderInLayout(string $view, array $data = [], string $pageTitle = ''): void
    {
        $data['_view']      = $view;
        $data['_pageTitle'] = $pageTitle;
        $this->render('shared/layout', $data);
    }

    //  Redirections

    protected function redirect(string $url): never
    {
        header('Location: /gestion-stock/public' . $url);
        exit;
    }

    protected function redirectBack(): never
    {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    // ---------------------------------------------------------------- Session

    protected function session(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    protected function sessionSet(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    protected function sessionForget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Flash : stocke un message et le détruit à la prochaine lecture */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    protected function getFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    // CSRF

    protected function generateCsrf(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
            http_response_code(419);
            die('Token CSRF invalide. Veuillez recharger la page et réessayer.');
        }
    }

    // Auth helpers

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    protected function requireRight(string $module, string $action): void
    {
        $this->requireAuth();
        $groupeId = $_SESSION['groupe_id'] ?? 0;

        $db = Database::getInstance();
        $ok = $db->fetchOne(
            'SELECT autorise FROM droit WHERE id_groupe = :g AND module = :m::t_module AND action = :a::t_action_droit',
            [':g' => $groupeId, ':m' => $module, ':a' => $action]
        );

        if (!$ok || !$ok['autorise']) {
            http_response_code(403);
            $title = 'Accès refusé';
            $code  = 403;
            include BASE_PATH . '/app/Shared/Views/error.php';
            exit;
        }
    }

    //  JSON helper

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //  Input helpers

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function inputInt(string $key, int $default = 0): int
    {
        return (int)($this->input($key, $default));
    }

    protected function inputFloat(string $key, float $default = 0.0): float
    {
        return (float)($this->input($key, $default));
    }

    protected function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
