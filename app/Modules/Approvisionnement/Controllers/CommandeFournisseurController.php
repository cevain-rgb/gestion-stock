<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Controllers;
use App\Core\Controller;
use App\Modules\Approvisionnement\Services\ApprovisionnementService;
use App\Modules\Structure\Models\ProduitModel;

class CommandeFournisseurController extends Controller
{
    private ApprovisionnementService $svc;
    public function __construct() { $this->svc = new ApprovisionnementService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','statut'=>$_GET['statut']??'',
                    'id_fournisseur'=>$_GET['fournisseur']??'','date_debut'=>$_GET['date_debut']??'','date_fin'=>$_GET['date_fin']??''];
        $data = $this->svc->listerCommandes(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/commandes/index', $data, 'Commandes fournisseurs');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $cmd = $this->svc->detailCommande((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande introuvable.'); $this->redirect('/approvisionnement/commandes'); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/commandes/show', compact('cmd'), $cmd['numero']);
    }

    public function creerForm(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer');
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/commandes/form', [
            'cmd'=>null,'errors'=>[],
            'fournisseurs'=>(new \App\Modules\Structure\Models\FournisseurModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouvelle commande fournisseur');
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer'); $this->verifyCsrf();
        $r = $this->svc->creerCommande($_POST, (int)$_SESSION['user_id']);
        if ($r['ok']) { $this->flash('success','Commande créée avec succès.'); $this->redirect('/approvisionnement/commandes/'.$r['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/commandes/form', [
            'cmd'=>null,'errors'=>$r['errors'],
            'fournisseurs'=>(new \App\Modules\Structure\Models\FournisseurModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouvelle commande fournisseur');
    }

    public function editForm(array $p=[]): void
    {
        $this->requireRight('approvisionnement','modifier');
        $cmd = $this->svc->detailCommande((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande introuvable.'); $this->redirect('/approvisionnement/commandes'); }
        if ($cmd['statut'] !== 'en_attente') { $this->flash('error','Seules les commandes en attente sont modifiables.'); $this->redirect('/approvisionnement/commandes/'.$p['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/commandes/form', [
            'cmd'=>$cmd,'errors'=>[],
            'fournisseurs'=>(new \App\Modules\Structure\Models\FournisseurModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Modifier la commande');
    }

    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('approvisionnement','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierLignesCommande((int)$p['id'], $_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande modifiée.':($r['message']??'Erreur.'));
        $this->redirect('/approvisionnement/commandes/'.$p['id']);
    }

    public function valider(array $p=[]): void
    {
        $this->requireRight('approvisionnement','modifier'); $this->verifyCsrf();
        $r = $this->svc->validerCommande((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande validée.':($r['message']??'Erreur.'));
        $this->redirect('/approvisionnement/commandes/'.$p['id']);
    }

    public function annuler(array $p=[]): void
    {
        $this->requireRight('approvisionnement','modifier'); $this->verifyCsrf();
        $r = $this->svc->annulerCommande((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande annulée.':($r['message']??'Erreur.'));
        $this->redirect('/approvisionnement/commandes/'.$p['id']);
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('approvisionnement','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerCommande((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/approvisionnement/commandes');
    }
}
