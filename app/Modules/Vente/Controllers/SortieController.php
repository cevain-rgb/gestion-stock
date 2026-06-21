<?php
declare(strict_types=1);
namespace App\Modules\Vente\Controllers;
use App\Core\Controller;
use App\Modules\Vente\Services\VenteService;

class SortieController extends Controller
{
    private VenteService $svc;
    public function __construct() { $this->svc = new VenteService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','motif'=>$_GET['motif']??''];
        $data = $this->svc->listerSorties(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Vente/sorties/index', $data, 'Bons de sortie');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $sortie = $this->svc->detailSortie((int)$p['id']);
        if (!$sortie) { $this->flash('error','Bon de sortie introuvable.'); $this->redirect('/vente/sorties'); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/sorties/show', compact('sortie'), $sortie['numero']);
    }

    public function creerForm(array $p=[]): void
    {
        $this->requireRight('vente','creer');
        $this->generateCsrf();
        $this->renderInLayout('Vente/sorties/form', [
            'errors'=>[], 'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouveau bon de sortie');
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('vente','creer'); $this->verifyCsrf();
        $r = $this->svc->creerSortie($_POST, (int)$_SESSION['user_id']);
        if ($r['ok']) { $this->flash('success','Bon de sortie enregistré. Le stock a été mis à jour.'); $this->redirect('/vente/sorties/'.$r['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/sorties/form', [
            'errors'=>$r['errors'], 'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouveau bon de sortie');
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('vente','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerSortie((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Bon de sortie supprimé.':($r['message']??'Erreur.'));
        $this->redirect('/vente/sorties');
    }
}
