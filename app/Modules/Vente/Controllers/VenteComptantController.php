<?php
declare(strict_types=1);
namespace App\Modules\Vente\Controllers;
use App\Core\Controller;
use App\Modules\Vente\Services\VenteService;
use App\Modules\Structure\Models\ClientModel;

class VenteComptantController extends Controller
{
    private VenteService $svc;
    public function __construct() { $this->svc = new VenteService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','comptant'=>'1','date_debut'=>$_GET['date_debut']??'','date_fin'=>$_GET['date_fin']??''];
        $data = $this->svc->listerCommandes(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Vente/comptant/index', $data, 'Vente comptant');
    }

    public function creerForm(array $p=[]): void
    {
        $this->requireRight('vente','creer');
        $this->generateCsrf();
        $this->renderInLayout('Vente/comptant/form', [
            'errors'=>[],
            'clients'=>(new ClientModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouvelle vente comptant');
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('vente','creer'); $this->verifyCsrf();
        $r = $this->svc->creerCommande($_POST, (int)$_SESSION['user_id'], true);
        if ($r['ok']) { $this->flash('success','Vente enregistrée avec succès.'); $this->redirect('/vente/commandes/'.$r['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/comptant/form', [
            'errors'=>$r['errors'],
            'clients'=>(new ClientModel())->tousOptions(),
            'produits'=>$this->svc->getProduitModel()->tous(1,1000,[]),
        ], 'Nouvelle vente comptant');
    }
}
