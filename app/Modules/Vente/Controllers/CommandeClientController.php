<?php
declare(strict_types=1);
namespace App\Modules\Vente\Controllers;
use App\Core\Controller;
use App\Modules\Vente\Services\VenteService;
use App\Modules\Structure\Models\ClientModel;

class CommandeClientController extends Controller
{
    private VenteService $svc;
    public function __construct() { $this->svc = new VenteService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','statut'=>$_GET['statut']??'',
                    'id_client'=>$_GET['client']??'','date_debut'=>$_GET['date_debut']??'','date_fin'=>$_GET['date_fin']??''];
        $data = $this->svc->listerCommandes(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Vente/commandes/index', $data, 'Commandes clients');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $cmd = $this->svc->detailCommande((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande introuvable.'); $this->redirect('/vente/commandes'); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/commandes/show', compact('cmd'), $cmd['numero']);
    }

    public function creerForm(array $p=[]): void
    {
        $this->requireRight('vente','creer');
        $this->generateCsrf();
        $this->renderInLayout('Vente/commandes/form', [
            'cmd'=>null,'errors'=>[],
            'clients'=>(new ClientModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouvelle commande client');
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('vente','creer'); $this->verifyCsrf();
        $r = $this->svc->creerCommande($_POST, (int)$_SESSION['user_id'], false);
        if ($r['ok']) { $this->flash('success','Commande créée avec succès.'); $this->redirect('/vente/commandes/'.$r['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/commandes/form', [
            'cmd'=>null,'errors'=>$r['errors'],
            'clients'=>(new ClientModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouvelle commande client');
    }

    public function editForm(array $p=[]): void
    {
        $this->requireRight('vente','modifier');
        $cmd = $this->svc->detailCommande((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande introuvable.'); $this->redirect('/vente/commandes'); }
        if ($cmd['statut'] !== 'en_attente') { $this->flash('error','Seules les commandes en attente sont modifiables.'); $this->redirect('/vente/commandes/'.$p['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/commandes/form', [
            'cmd'=>$cmd,'errors'=>[],
            'clients'=>(new ClientModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Modifier la commande');
    }

    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('vente','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierLignesCommande((int)$p['id'], $_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande modifiée.':($r['message']??'Erreur.'));
        $this->redirect('/vente/commandes/'.$p['id']);
    }

    public function valider(array $p=[]): void
    {
        $this->requireRight('vente','modifier'); $this->verifyCsrf();
        $r = $this->svc->validerCommande((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande validée.':($r['message']??'Erreur.'));
        $this->redirect('/vente/commandes/'.$p['id']);
    }

    public function annuler(array $p=[]): void
    {
        $this->requireRight('vente','modifier'); $this->verifyCsrf();
        $r = $this->svc->annulerCommande((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande annulée.':($r['message']??'Erreur.'));
        $this->redirect('/vente/commandes/'.$p['id']);
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('vente','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerCommande((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Commande supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/vente/commandes');
    }
}
