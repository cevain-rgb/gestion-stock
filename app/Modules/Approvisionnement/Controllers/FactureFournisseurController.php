<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Controllers;
use App\Core\Controller;
use App\Modules\Approvisionnement\Services\ApprovisionnementService;
use App\Modules\Structure\Models\BanqueModel;

class FactureFournisseurController extends Controller
{
    private ApprovisionnementService $svc;
    public function __construct() { $this->svc = new ApprovisionnementService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','statut'=>$_GET['statut']??'','date_debut'=>$_GET['date_debut']??'','date_fin'=>$_GET['date_fin']??''];
        $data = $this->svc->listerFactures(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/factures/index', $data, 'Factures fournisseurs');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $facture = $this->svc->detailFacture((int)$p['id']);
        if (!$facture) { $this->flash('error','Facture introuvable.'); $this->redirect('/approvisionnement/factures'); }
        $banques = (new BanqueModel())->toutesOptions();
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/factures/show', compact('facture','banques'), $facture['numero']);
    }

    public function creerForm(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer');
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/factures/form', [
            'errors'=>[], 'commandes'=>$this->svc->commandesFacturables(),
        ], 'Nouvelle facture fournisseur');
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer'); $this->verifyCsrf();
        $r = $this->svc->creerFacture($_POST, (int)$_SESSION['user_id']);
        if ($r['ok']) { $this->flash('success','Facture créée avec succès.'); $this->redirect('/approvisionnement/factures/'.$r['id']); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/factures/form', [
            'errors'=>$r['errors'], 'commandes'=>$this->svc->commandesFacturables(),
        ], 'Nouvelle facture fournisseur');
    }

    public function reglementTraiter(array $p=[]): void
    {
        $this->requireRight('approvisionnement','regler'); $this->verifyCsrf();
        $r = $this->svc->ajouterReglement((int)$p['id'], $_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Règlement enregistré.':(implode(' ',$r['errors']??[])));
        $this->redirect('/approvisionnement/factures/'.$p['id']);
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('approvisionnement','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerFacture((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Facture supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/approvisionnement/factures');
    }
}
