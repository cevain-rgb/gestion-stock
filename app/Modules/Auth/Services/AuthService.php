<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\AuthModel;

class AuthService
{
    private AuthModel $model;
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 300; // 5 min

    public function __construct()
    {
        $this->model = new AuthModel();
    }

    /**
     * Tente une connexion. Retourne true si succès, false si échec.
     * En cas de succès, remplit la session.
     */
    public function connecter(string $login, string $motDePasse): bool
    {
        // Vérification brute-force
        if ($this->estBloque($login)) return false;

        $valide = $this->model->verifierIdentifiants($login, $motDePasse);

        if (!$valide) {
            $this->incrementerEchecs($login);
            return false;
        }

        $user = $this->model->trouverParLogin($login);
        if (!$user) return false;

        // Régénérer la session pour éviter la fixation
        session_regenerate_id(true);

        // Remplir la session
        $_SESSION['user_id']       = $user['id_utilisateur'];
        $_SESSION['user_nom']      = $user['nom'];
        $_SESSION['user_prenom']   = $user['prenom'];
        $_SESSION['user_login']    = $user['login'];
        $_SESSION['groupe_id']     = $user['id_groupe'];
        $_SESSION['groupe_libelle']= $user['groupe_libelle'];
        $_SESSION['droits']        = $this->model->droitsGroupe($user['id_groupe']);
        $_SESSION['_created']      = time();

        // Générer le token CSRF
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));

        // Réinitialiser les compteurs d'échecs
        $this->reinitialiserEchecs($login);

        // Journaliser
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->model->journaliser($user['id_utilisateur'], 'CONNEXION', $ip);

        return true;
    }

    public function deconnecter(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if ($userId) {
            $this->model->journaliser((int)$userId, 'DECONNEXION', $ip);
        }

        \App\Core\Session::destroy();
    }

    public function estAuthentifie(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public function aDroit(string $module, string $action): bool
    {
        return (bool)($_SESSION['droits'][$module . '__' . $action] ?? false);
    }

    // ─── Brute-force ─────────────────────────────────────────────────────────

    private function cleEchecs(string $login): string
    {
        return '_bf_' . md5($login);
    }

    private function estBloque(string $login): bool
    {
        $key  = $this->cleEchecs($login);
        $data = $_SESSION[$key] ?? null;
        if (!$data) return false;
        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            if (time() - $data['last'] < self::LOCKOUT_SECONDS) return true;
            // Délai écoulé : réinitialiser
            $this->reinitialiserEchecs($login);
        }
        return false;
    }

    private function incrementerEchecs(string $login): void
    {
        $key = $this->cleEchecs($login);
        $_SESSION[$key] = [
            'attempts' => ($_SESSION[$key]['attempts'] ?? 0) + 1,
            'last'     => time(),
        ];
    }

    private function reinitialiserEchecs(string $login): void
    {
        unset($_SESSION[$this->cleEchecs($login)]);
    }

    public function tentativesRestantes(string $login): int
    {
        $key = $this->cleEchecs($login);
        $att = $_SESSION[$key]['attempts'] ?? 0;
        return max(0, self::MAX_ATTEMPTS - $att);
    }

    public function secondesAttente(string $login): int
    {
        $key  = $this->cleEchecs($login);
        $data = $_SESSION[$key] ?? null;
        if (!$data || $data['attempts'] < self::MAX_ATTEMPTS) return 0;
        $elapsed = time() - $data['last'];
        return max(0, self::LOCKOUT_SECONDS - $elapsed);
    }
}
