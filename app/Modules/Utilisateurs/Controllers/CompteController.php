<?php
declare(strict_types=1);
namespace App\Modules\Utilisateurs\Controllers;

use App\Core\Controller;
use App\Modules\Utilisateurs\Services\UtilisateurService;
use App\Modules\Utilisateurs\Models\UtilisateurModel;
use App\Modules\Utilisateurs\Models\GroupeModel;

class CompteController extends Controller
{
    private UtilisateurService $service;

    public function __construct() { $this->service = new UtilisateurService(); }

    /** GET /utilisateurs/comptes */
    public function index(array $p = []): void
    {
        $this->requireRight('securite', 'consulter');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filtres = [
            'recherche' => $_GET['q']      ?? '',
            'actif'     => $_GET['actif']  ?? '',
            'id_groupe' => $_GET['groupe'] ?? '',
        ];
        $data    = $this->service->listerUtilisateurs($page, $filtres);
        $groupes = (new GroupeModel())->tous();
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/comptes/index',
            array_merge($data, ['groupes' => $groupes]), 'Utilisateurs');
    }

    /** GET /utilisateurs/comptes/creer */
    public function creerForm(array $p = []): void
    {
        $this->requireRight('securite', 'creer');
        $groupes = (new GroupeModel())->tous();
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/comptes/form',
            ['utilisateur' => null, 'groupes' => $groupes, 'errors' => []], 'Nouvel utilisateur');
    }

    /** POST /utilisateurs/comptes/creer */
    public function creerTraiter(array $p = []): void
    {
        $this->requireRight('securite', 'creer');
        $this->verifyCsrf();
        $result = $this->service->creerUtilisateur($_POST);
        if ($result['ok']) {
            $this->flash('success', 'Utilisateur créé avec succès.');
            $this->redirect('/utilisateurs/comptes');
        }
        $groupes = (new GroupeModel())->tous();
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/comptes/form',
            ['utilisateur' => null, 'groupes' => $groupes, 'errors' => $result['errors']],
            'Nouvel utilisateur');
    }

    /** GET /utilisateurs/comptes/:id */
    public function show(array $p = []): void
    {
        $this->requireRight('securite', 'consulter');
        $u = (new UtilisateurModel())->trouverParId((int)$p['id']);
        if (!$u) { $this->flash('error', 'Utilisateur introuvable.'); $this->redirect('/utilisateurs/comptes'); }
        $historique = (new UtilisateurModel())->historiqueConnexions((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/comptes/show',
            compact('u', 'historique'), $u['prenom'] . ' ' . $u['nom']);
    }

    /** GET /utilisateurs/comptes/:id/edit */
    public function editForm(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $u = (new UtilisateurModel())->trouverParId((int)$p['id']);
        if (!$u) { $this->flash('error', 'Utilisateur introuvable.'); $this->redirect('/utilisateurs/comptes'); }
        $groupes = (new GroupeModel())->tous();
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/comptes/form',
            ['utilisateur' => $u, 'groupes' => $groupes, 'errors' => []], 'Modifier l\'utilisateur');
    }

    /** POST /utilisateurs/comptes/:id/edit */
    public function editTraiter(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $this->verifyCsrf();
        $result = $this->service->modifierUtilisateur((int)$p['id'], $_POST);
        if ($result['ok']) {
            $this->flash('success', 'Utilisateur modifié avec succès.');
            $this->redirect('/utilisateurs/comptes');
        }
        $u       = (new UtilisateurModel())->trouverParId((int)$p['id']);
        $groupes = (new GroupeModel())->tous();
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/comptes/form',
            ['utilisateur' => $u, 'groupes' => $groupes, 'errors' => $result['errors']],
            'Modifier l\'utilisateur');
    }

    /** POST /utilisateurs/comptes/:id/actif */
    public function basculerActif(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $this->verifyCsrf();
        $result = $this->service->basculerActif((int)$p['id']);
        $this->flash($result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Statut modifié.' : ($result['message'] ?? 'Erreur.'));
        $this->redirect('/utilisateurs/comptes');
    }

    /** POST /utilisateurs/comptes/:id/mdp */
    public function changerMdp(array $p = []): void
    {
        $this->requireRight('securite', 'modifier');
        $this->verifyCsrf();
        $result = $this->service->changerMotDePasse((int)$p['id'], $_POST);
        if ($result['ok']) {
            $this->flash('success', 'Mot de passe modifié.');
            $this->redirect('/utilisateurs/comptes/' . $p['id']);
        }
        $u       = (new UtilisateurModel())->trouverParId((int)$p['id']);
        $historique = (new UtilisateurModel())->historiqueConnexions((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Utilisateurs/comptes/show',
            ['u' => $u, 'historique' => $historique, 'mdpErrors' => $result['errors']],
            $u['prenom'] . ' ' . $u['nom']);
    }

    /** POST /utilisateurs/comptes/:id/supprimer */
    public function supprimer(array $p = []): void
    {
        $this->requireRight('securite', 'supprimer');
        $this->verifyCsrf();
        $result = $this->service->supprimerUtilisateur((int)$p['id']);
        $this->flash($result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Utilisateur supprimé.' : ($result['message'] ?? 'Erreur.'));
        $this->redirect('/utilisateurs/comptes');
    }
}
