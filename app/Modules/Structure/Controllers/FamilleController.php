<?php
declare(strict_types=1);
namespace App\Modules\Structure\Controllers;
use App\Core\Controller;
use App\Modules\Structure\Services\StructureService;

class FamilleController extends Controller
{
    private StructureService $svc;
    public function __construct() { $this->svc = new StructureService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $data = $this->svc->listerFamilles(max(1,(int)($_GET['page']??1)), $_GET['q']??'');
        $this->generateCsrf();
        $this->renderInLayout('Structure/familles/index', $data, 'Familles de produits');
    }
    public function creerForm(array $p=[]): void
    {
        $this->requireRight('structure','creer');
        $this->generateCsrf();
        $this->renderInLayout('Structure/familles/form', ['famille'=>null,'errors'=>[]], 'Nouvelle famille');
    }
    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->verifyCsrf();
        $r = $this->svc->creerFamille($_POST);
        if ($r['ok']) { $this->flash('success','Famille créée.'); $this->redirect('/structure/familles'); }
        $this->generateCsrf();
        $this->renderInLayout('Structure/familles/form', ['famille'=>null,'errors'=>$r['errors']], 'Nouvelle famille');
    }
    public function editForm(array $p=[]): void
    {
        $this->requireRight('structure','modifier');
        $f = $this->svc->getFamilleModel()->trouverParId((int)$p['id']);
        if (!$f) { $this->flash('error','Famille introuvable.'); $this->redirect('/structure/familles'); }
        $this->generateCsrf();
        $this->renderInLayout('Structure/familles/form', ['famille'=>$f,'errors'=>[]], 'Modifier famille');
    }
    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('structure','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierFamille((int)$p['id'], $_POST);
        if ($r['ok']) { $this->flash('success','Famille modifiée.'); $this->redirect('/structure/familles'); }
        $f = $this->svc->getFamilleModel()->trouverParId((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/familles/form', ['famille'=>$f,'errors'=>$r['errors']], 'Modifier famille');
    }
    public function supprimer(array $p=[]): void
    {
        $this->requireRight('structure','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerFamille((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Famille supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/structure/familles');
    }
}
