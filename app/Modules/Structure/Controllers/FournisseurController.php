<?php
declare(strict_types=1);
namespace App\Modules\Structure\Controllers;
use App\Core\Controller;
use App\Modules\Structure\Services\StructureService;

class FournisseurController extends Controller
{
    private StructureService $svc;
    public function __construct() { $this->svc = new StructureService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $data = $this->svc->listerFournisseurs(max(1,(int)($_GET['page']??1)), $_GET['q']??'');
        $this->generateCsrf();
        $this->renderInLayout('Structure/fournisseurs/index', $data, 'Fournisseurs');
    }
    public function show(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $fournisseur = $this->svc->getFournisseurModel()->trouverParId((int)$p['id']);
        if (!$fournisseur) { $this->flash('error','Fournisseur introuvable.'); $this->redirect('/structure/fournisseurs'); }
        $commandes = $this->svc->getFournisseurModel()->dernieresCommandes((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/fournisseurs/show', compact('fournisseur','commandes'), $fournisseur['nom']);
    }
    public function creerForm(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->generateCsrf();
        $this->renderInLayout('Structure/fournisseurs/form', ['fournisseur'=>null,'errors'=>[]], 'Nouveau fournisseur');
    }
    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->verifyCsrf();
        $r = $this->svc->creerFournisseur($_POST);
        if ($r['ok']) { $this->flash('success','Fournisseur créé.'); $this->redirect('/structure/fournisseurs'); }
        $this->generateCsrf();
        $this->renderInLayout('Structure/fournisseurs/form', ['fournisseur'=>null,'errors'=>$r['errors']], 'Nouveau fournisseur');
    }
    public function editForm(array $p=[]): void
    {
        $this->requireRight('structure','modifier');
        $f = $this->svc->getFournisseurModel()->trouverParId((int)$p['id']);
        if (!$f) { $this->flash('error','Fournisseur introuvable.'); $this->redirect('/structure/fournisseurs'); }
        $this->generateCsrf();
        $this->renderInLayout('Structure/fournisseurs/form', ['fournisseur'=>$f,'errors'=>[]], 'Modifier fournisseur');
    }
    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('structure','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierFournisseur((int)$p['id'], $_POST);
        if ($r['ok']) { $this->flash('success','Fournisseur modifié.'); $this->redirect('/structure/fournisseurs'); }
        $f = $this->svc->getFournisseurModel()->trouverParId((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/fournisseurs/form', ['fournisseur'=>$f,'errors'=>$r['errors']], 'Modifier fournisseur');
    }
    public function supprimer(array $p=[]): void
    {
        $this->requireRight('structure','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerFournisseur((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Fournisseur supprimé.':($r['message']??'Erreur.'));
        $this->redirect('/structure/fournisseurs');
    }
}
