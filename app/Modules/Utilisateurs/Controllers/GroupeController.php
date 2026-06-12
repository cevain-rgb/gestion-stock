<?php
declare(strict_types=1);
namespace App\Modules\Utilisateurs\Controllers;

use App\Core\Controller;
use App\Modules\Utilisateurs\Services\UtilisateurService;
use App\Modules\Utilisateurs\Models\GroupeModel;

class GroupeController extends Controller
{
    private UtilisateurService $service;

    public function __construct()
    {
        $this->service = new UtilisateurService();
    }

    /** GET /utilisateurs/groupes */
    public function index(array $p = []): void
    {
        $this->requireRight('securite', 'consulter');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $data   = $this->service->listerGroupes($page);
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/groupes/index', $data, 'Groupes d\'utilisateurs');
    }

    /** GET /utilisateurs/groupes/creer */
    public function creerForm(array $p = []): void
    {
        $this->requireRight('securite', 'creer');
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/groupes/form', ['groupe' => null, 'errors' => []], 'Nouveau groupe');
    }

    /** POST /utilisateurs/groupes/creer */
    public function creerTraiter(array $p = []): void
    {
        $this->requireRight('securite', 'creer');
        $this->verifyCsrf();

        $result = $this->service->creerGroupe($_POST);
        if ($result['ok']) {
            $this->flash('success', 'Groupe créé avec succès.');
            $this->redirect('/utilisateurs/groupes');
        }
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/groupes/form',
            ['groupe' => null, 'errors' => $result['errors']],
            'Nouveau groupe');
    }

    /** GET /utilisateurs/groupes/:id/edit */
    public function editForm(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $groupe = (new GroupeModel())->trouverParId((int)$p['id']);
        if (!$groupe) { $this->flash('error', 'Groupe introuvable.'); $this->redirect('/utilisateurs/groupes'); }
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/groupes/form', ['groupe' => $groupe, 'errors' => []], 'Modifier le groupe');
    }

    /** POST /utilisateurs/groupes/:id/edit */
    public function editTraiter(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $this->verifyCsrf();
        $result = $this->service->modifierGroupe((int)$p['id'], $_POST);
        if ($result['ok']) {
            $this->flash('success', 'Groupe modifié avec succès.');
            $this->redirect('/utilisateurs/groupes');
        }
        $groupe = (new GroupeModel())->trouverParId((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/groupes/form',
            ['groupe' => $groupe, 'errors' => $result['errors']],
            'Modifier le groupe');
    }

    /** POST /utilisateurs/groupes/:id/supprimer */
    public function supprimer(array $p = []): void
    {
        $this->requireRight('securite', 'supprimer');
        $this->verifyCsrf();
        $result = $this->service->supprimerGroupe((int)$p['id']);
        $this->flash($result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Groupe supprimé.' : ($result['message'] ?? 'Erreur.'));
        $this->redirect('/utilisateurs/groupes');
    }

    /** GET /utilisateurs/groupes/:id/droits */
    public function droitsForm(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $groupe  = (new GroupeModel())->trouverParId((int)$p['id']);
        if (!$groupe) { $this->flash('error', 'Groupe introuvable.'); $this->redirect('/utilisateurs/groupes'); }
        $matrice = $this->service->matriceDroits((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/droits/matrice',
            compact('groupe', 'matrice'), 'Droits - ' . $groupe['libelle']);
    }

    /** POST /utilisateurs/groupes/:id/droits */
    public function droitsTraiter(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $this->verifyCsrf();
        $this->service->sauvegarderDroits((int)$p['id'], $_POST);
        $this->flash('success', 'Droits enregistrés avec succès.');
        $this->redirect('/utilisateurs/groupes/' . $p['id'] . '/droits');
    }
}
