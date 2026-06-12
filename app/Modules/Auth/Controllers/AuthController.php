<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    /** GET /login */
    public function loginForm(array $params = []): void
    {
        if ($this->service->estAuthentifie()) {
            $this->redirect('/dashboard');
        }
        // Générer le CSRF avant affichage du formulaire
        $this->generateCsrf();
        $this->render('Auth/login', ['pageTitle' => 'Connexion']);
    }

    /** POST /login */
    public function loginTraiter(array $params = []): void
    {
        $this->verifyCsrf();

        $login = trim($this->input('login', ''));
        $mdp   = $this->input('password', '');

        // Vérification compte bloqué
        $attente = $this->service->secondesAttente($login);
        if ($attente > 0) {
            $this->flash('error', "Trop de tentatives. Réessayez dans {$attente} secondes.");
            $this->redirect('/login');
        }

        if ($this->service->connecter($login, $mdp)) {
            $this->redirect('/dashboard');
        }

        $restantes = $this->service->tentativesRestantes($login);
        $msg = 'Identifiants incorrects.';
        if ($restantes > 0) {
            $msg .= " Il vous reste {$restantes} tentative(s).";
        } else {
            $msg = 'Compte temporairement bloqué. Réessayez dans 5 minutes.';
        }

        $this->flash('error', $msg);
        $this->redirect('/login');
    }

    /** POST /logout */
    public function logout(array $params = []): void
    {
        $this->service->deconnecter();
        $this->redirect('/login');
    }
}
