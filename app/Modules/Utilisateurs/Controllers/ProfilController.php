<?php
declare(strict_types=1);
namespace App\Modules\Utilisateurs\Controllers;

use App\Core\Controller;
use App\Modules\Utilisateurs\Services\UtilisateurService;

class ProfilController extends Controller
{
    private UtilisateurService $service;
    public function __construct() { $this->service = new UtilisateurService(); }

    /** GET /profil */
    public function index(array $p = []): void
    {
        $this->requireAuth();
        $profil     = $this->service->profilConnecte();
        $historique = $this->service->historiqueConnexions();
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/profil/index',
            compact('profil', 'historique'), 'Mon profil');
    }

    /** POST /profil/mdp */
    public function changerMdp(array $p = []): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $result = $this->service->changerMotDePasse((int)$_SESSION['user_id'], $_POST);
        if ($result['ok']) {
            $this->flash('success', 'Mot de passe mis à jour.');
        } else {
            $this->flash('error', implode(' ', $result['errors']));
        }
        $this->redirect('/profil');
    }
}
