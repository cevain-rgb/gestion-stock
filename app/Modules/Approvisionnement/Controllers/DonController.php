<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Controllers;
use App\Core\Controller;
use App\Modules\Approvisionnement\Services\ApprovisionnementService;

class DonController extends Controller
{
    private ApprovisionnementService $svc;
    public function __construct() { $this->svc = new ApprovisionnementService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $filtres = ['recherche'=>$_GET['q']??''];
        $data = $this->svc->listerDons(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/dons/index', $data, 'Dons');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $don = $this->svc->detailDon((int)$p['id']);
        if (!$don) { $this->flash('error','Don introuvable.'); $this->redirect('/approvisionnement/dons'); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/dons/show', compact('don'), $don['numero']);
    }

    public function creerForm(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer');
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/dons/form', [
            'errors'=>[],
            'fournisseurs'=>(new \App\Modules\Structure\Models\FournisseurModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouveau don');
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer'); $this->verifyCsrf();
        $r = $this->svc->creerDon($_POST, (int)$_SESSION['user_id']);
        if ($r['ok']) { $this->flash('success','Don enregistré. Le stock a été mis à jour.'); $this->redirect('/approvisionnement/dons/'.$r['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/dons/form', [
            'errors'=>$r['errors'],
            'fournisseurs'=>(new \App\Modules\Structure\Models\FournisseurModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouveau don');
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('approvisionnement','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerDon((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Don supprimé.':($r['message']??'Erreur.'));
        $this->redirect('/approvisionnement/dons');
    }
}
