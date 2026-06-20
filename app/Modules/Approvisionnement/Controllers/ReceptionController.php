<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Controllers;
use App\Core\Controller;
use App\Modules\Approvisionnement\Services\ApprovisionnementService;

class ReceptionController extends Controller
{
    private ApprovisionnementService $svc;
    public function __construct() { $this->svc = new ApprovisionnementService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','date_debut'=>$_GET['date_debut']??'','date_fin'=>$_GET['date_fin']??''];
        $data = $this->svc->listerReceptions(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/receptions/index', $data, 'Réceptions');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('approvisionnement','consulter');
        $reception = $this->svc->detailReception((int)$p['id']);
        if (!$reception) { $this->flash('error','Réception introuvable.'); $this->redirect('/approvisionnement/receptions'); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/receptions/show', compact('reception'), $reception['numero']);
    }

    /** GET /approvisionnement/commandes/:id/recevoir */
    public function creerForm(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer');
        $cmd = $this->svc->preparerReception((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande non disponible pour réception (doit être validée).'); $this->redirect('/approvisionnement/commandes'); }
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/receptions/form', ['cmd'=>$cmd,'errors'=>[]], 'Réception — '.$cmd['numero']);
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('approvisionnement','creer'); $this->verifyCsrf();
        $r = $this->svc->creerReception((int)$p['id'], $_POST, (int)$_SESSION['user_id']);
        if ($r['ok']) { $this->flash('success','Réception enregistrée. Le stock a été mis à jour.'); $this->redirect('/approvisionnement/receptions/'.$r['id']); }
        $cmd = $this->svc->preparerReception((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Approvisionnement/receptions/form', ['cmd'=>$cmd,'errors'=>$r['errors']], 'Réception');
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('approvisionnement','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerReception((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Réception supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/approvisionnement/receptions');
    }
}
