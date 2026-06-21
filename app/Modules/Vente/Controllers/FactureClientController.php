<?php
declare(strict_types=1);
namespace App\Modules\Vente\Controllers;
use App\Core\Controller;
use App\Modules\Vente\Services\VenteService;
use App\Modules\Structure\Models\BanqueModel;

class FactureClientController extends Controller
{
    private VenteService $svc;
    public function __construct() { $this->svc = new VenteService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','statut'=>$_GET['statut']??'','date_debut'=>$_GET['date_debut']??'','date_fin'=>$_GET['date_fin']??''];
        $data = $this->svc->listerFactures(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Vente/factures/index', $data, 'Factures clients');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $facture = $this->svc->detailFacture((int)$p['id']);
        if (!$facture) { $this->flash('error','Facture introuvable.'); $this->redirect('/vente/factures'); }
        $banques = (new BanqueModel())->toutesOptions();
        $this->generateCsrf();
        $this->renderInLayout('Vente/factures/show', compact('facture','banques'), $facture['numero']);
    }

    public function creerForm(array $p=[]): void
    {
        $this->requireRight('vente','creer');
        $this->generateCsrf();
        $this->renderInLayout('Vente/factures/form', [
            'errors'=>[], 'commandes'=>$this->svc->commandesFacturables(),
        ], 'Nouvelle facture client');
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('vente','creer'); $this->verifyCsrf();
        $r = $this->svc->creerFacture($_POST, (int)$_SESSION['user_id']);
        if ($r['ok']) { $this->flash('success','Facture créée avec succès.'); $this->redirect('/vente/factures/'.$r['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/factures/form', [
            'errors'=>$r['errors'], 'commandes'=>$this->svc->commandesFacturables(),
        ], 'Nouvelle facture client');
    }

    public function reglementTraiter(array $p=[]): void
    {
        $this->requireRight('vente','regler'); $this->verifyCsrf();
        $r = $this->svc->ajouterReglement((int)$p['id'], $_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Règlement enregistré.':(implode(' ',$r['errors']??[])));
        $this->redirect('/vente/factures/'.$p['id']);
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('vente','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerFacture((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Facture supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/vente/factures');
    }
}
