<?php
declare(strict_types=1);
namespace App\Modules\Vente\Controllers;
use App\Core\Controller;
use App\Modules\Vente\Services\VenteService;

class LivraisonController extends Controller
{
    private VenteService $svc;
    public function __construct() { $this->svc = new VenteService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $filtres = ['recherche'=>$_GET['q']??'','date_debut'=>$_GET['date_debut']??'','date_fin'=>$_GET['date_fin']??''];
        $data = $this->svc->listerLivraisons(max(1,(int)($_GET['page']??1)), $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Vente/livraisons/index', $data, 'Livraisons');
    }

    public function show(array $p=[]): void
    {
        $this->requireRight('vente','consulter');
        $livraison = $this->svc->detailLivraison((int)$p['id']);
        if (!$livraison) { $this->flash('error','Livraison introuvable.'); $this->redirect('/vente/livraisons'); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/livraisons/show', compact('livraison'), $livraison['numero']);
    }

    /** GET /vente/commandes/:id/livrer */
    public function creerForm(array $p=[]): void
    {
        $this->requireRight('vente','creer');
        $cmd = $this->svc->preparerLivraison((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande non disponible pour livraison (doit être validée).'); $this->redirect('/vente/commandes'); }
        $this->generateCsrf();
        $this->renderInLayout('Vente/livraisons/form', ['cmd'=>$cmd,'errors'=>[]], 'Livraison — '.$cmd['numero']);
    }

    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('vente','creer'); $this->verifyCsrf();
        $r = $this->svc->creerLivraison((int)$p['id'], $_POST, (int)$_SESSION['user_id']);
        if ($r['ok']) { $this->flash('success','Livraison enregistrée. Le stock a été mis à jour.'); $this->redirect('/vente/livraisons/'.$r['id']); }
        $cmd = $this->svc->preparerLivraison((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Vente/livraisons/form', ['cmd'=>$cmd,'errors'=>$r['errors']], 'Livraison');
    }

    public function supprimer(array $p=[]): void
    {
        $this->requireRight('vente','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerLivraison((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Livraison supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/vente/livraisons');
    }
}
