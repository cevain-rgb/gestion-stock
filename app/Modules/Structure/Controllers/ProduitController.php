<?php
declare(strict_types=1);
namespace App\Modules\Structure\Controllers;
use App\Core\Controller;
use App\Modules\Structure\Services\StructureService;

class ProduitController extends Controller
{
    private StructureService $svc;
    public function __construct() { $this->svc = new StructureService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','id_famille'=>$_GET['famille']??'',
                    'alerte'=>$_GET['alerte']??'','rupture'=>$_GET['rupture']??''];
        $data = $this->svc->listerProduits(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Structure/produits/index', $data, 'Produits');
    }
    public function show(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $produit = $this->svc->getProduitModel()->trouverParId((int)$p['id']);
        if (!$produit) { $this->flash('error','Produit introuvable.'); $this->redirect('/structure/produits'); }
        $fils = $this->svc->getProduitModel()->fils((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/produits/show', compact('produit','fils'), $produit['designation']);
    }
    public function creerForm(array $p=[]): void
    {
        $this->requireRight('structure','creer');
        $this->generateCsrf();
        $this->renderInLayout('Structure/produits/form',
            ['produit'=>null,'errors'=>[],
             'familles'=>$this->svc->getFamilleModel()->toutesOptions(),
             'parents'=>$this->svc->getProduitModel()->produitsParents()], 'Nouveau produit');
    }
    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->verifyCsrf();
        $r = $this->svc->creerProduit($_POST);
        if ($r['ok']) { $this->flash('success','Produit créé.'); $this->redirect('/structure/produits'); }
        $this->generateCsrf();
        $this->renderInLayout('Structure/produits/form',
            ['produit'=>null,'errors'=>$r['errors'],
             'familles'=>$this->svc->getFamilleModel()->toutesOptions(),
             'parents'=>$this->svc->getProduitModel()->produitsParents()], 'Nouveau produit');
    }
    public function editForm(array $p=[]): void
    {
        $this->requireRight('structure','modifier');
        $produit = $this->svc->getProduitModel()->trouverParId((int)$p['id']);
        if (!$produit) { $this->flash('error','Produit introuvable.'); $this->redirect('/structure/produits'); }
        $this->generateCsrf();
        $this->renderInLayout('Structure/produits/form',
            ['produit'=>$produit,'errors'=>[],
             'familles'=>$this->svc->getFamilleModel()->toutesOptions(),
             'parents'=>$this->svc->getProduitModel()->produitsParents()], 'Modifier produit');
    }
    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('structure','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierProduit((int)$p['id'], $_POST);
        if ($r['ok']) { $this->flash('success','Produit modifié.'); $this->redirect('/structure/produits'); }
        $produit = $this->svc->getProduitModel()->trouverParId((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/produits/form',
            ['produit'=>$produit,'errors'=>$r['errors'],
             'familles'=>$this->svc->getFamilleModel()->toutesOptions(),
             'parents'=>$this->svc->getProduitModel()->produitsParents()], 'Modifier produit');
    }
    public function supprimer(array $p=[]): void
    {
        $this->requireRight('structure','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerProduit((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Produit supprimé.':($r['message']??'Erreur.'));
        $this->redirect('/structure/produits');
    }
}
