<?php
declare(strict_types=1);
namespace App\Modules\Structure\Controllers;
use App\Core\Controller;
use App\Modules\Structure\Services\StructureService;

class ClientController extends Controller
{
    private StructureService $svc;
    public function __construct() { $this->svc = new StructureService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','id_categorie'=>$_GET['categorie']??''];
        $data = $this->svc->listerClients(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Structure/clients/index', $data, 'Clients');
    }
    public function show(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $client = $this->svc->getClientModel()->trouverParId((int)$p['id']);
        if (!$client) { $this->flash('error','Client introuvable.'); $this->redirect('/structure/clients'); }
        $commandes = $this->svc->getClientModel()->dernieresCommandes((int)$p['id']);
        $solde     = $this->svc->getClientModel()->soldeDu((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/clients/show', compact('client','commandes','solde'), $client['nom']);
    }
    public function creerForm(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->generateCsrf();
        $cats = $this->svc->getCategorieModel()->toutesOptions();
        $this->renderInLayout('Structure/clients/form', ['client'=>null,'categories'=>$cats,'errors'=>[]], 'Nouveau client');
    }
    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->verifyCsrf();
        $r = $this->svc->creerClient($_POST);
        if ($r['ok']) { $this->flash('success','Client créé.'); $this->redirect('/structure/clients'); }
        $cats = $this->svc->getCategorieModel()->toutesOptions();
        $this->generateCsrf();
        $this->renderInLayout('Structure/clients/form', ['client'=>null,'categories'=>$cats,'errors'=>$r['errors']], 'Nouveau client');
    }
    public function editForm(array $p=[]): void
    {
        $this->requireRight('structure','modifier');
        $c = $this->svc->getClientModel()->trouverParId((int)$p['id']);
        if (!$c) { $this->flash('error','Client introuvable.'); $this->redirect('/structure/clients'); }
        $cats = $this->svc->getCategorieModel()->toutesOptions();
        $this->generateCsrf();
        $this->renderInLayout('Structure/clients/form', ['client'=>$c,'categories'=>$cats,'errors'=>[]], 'Modifier client');
    }
    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('structure','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierClient((int)$p['id'], $_POST);
        if ($r['ok']) { $this->flash('success','Client modifié.'); $this->redirect('/structure/clients'); }
        $c    = $this->svc->getClientModel()->trouverParId((int)$p['id']);
        $cats = $this->svc->getCategorieModel()->toutesOptions();
        $this->generateCsrf();
        $this->renderInLayout('Structure/clients/form', ['client'=>$c,'categories'=>$cats,'errors'=>$r['errors']], 'Modifier client');
    }
    public function supprimer(array $p=[]): void
    {
        $this->requireRight('structure','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerClient((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Client supprimé.':($r['message']??'Erreur.'));
        $this->redirect('/structure/clients');
    }
}
