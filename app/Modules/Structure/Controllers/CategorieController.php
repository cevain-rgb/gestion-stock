<?php
declare(strict_types=1);
namespace App\Modules\Structure\Controllers;
use App\Core\Controller;
use App\Modules\Structure\Services\StructureService;

class CategorieController extends Controller
{
    private StructureService $svc;
    public function __construct() { $this->svc = new StructureService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $categories = $this->svc->listerCategories();
        $this->generateCsrf();
        $this->renderInLayout('Structure/categories/index', compact('categories'), 'Catégories clients');
    }
    public function creerForm(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->generateCsrf();
        $this->renderInLayout('Structure/categories/form', ['categorie'=>null,'errors'=>[]], 'Nouvelle catégorie');
    }
    public function editForm(array $p=[]): void
    {
        $this->requireRight('structure','modifier');
        $categorie = $this->svc->getCategorieModel()->trouverParId((int)$p['id']);
        if (!$categorie) { $this->flash('error','Catégorie introuvable.'); $this->redirect('/structure/categories'); }
        $this->generateCsrf();
        $this->renderInLayout('Structure/categories/form', ['categorie'=>$categorie,'errors'=>[]], 'Modifier la catégorie');
    }
    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->verifyCsrf();
        $r = $this->svc->creerCategorie($_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Catégorie créée.':(implode(' ',$r['errors']??[])));
        $this->redirect('/structure/categories');
    }
    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('structure','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierCategorie((int)$p['id'], $_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Catégorie modifiée.':(implode(' ',$r['errors']??[])));
        $this->redirect('/structure/categories');
    }
    public function supprimer(array $p=[]): void
    {
        $this->requireRight('structure','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerCategorie((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Catégorie supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/structure/categories');
    }
}
