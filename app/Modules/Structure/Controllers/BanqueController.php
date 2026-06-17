<?php
declare(strict_types=1);
namespace App\Modules\Structure\Controllers;
use App\Core\Controller;
use App\Modules\Structure\Services\StructureService;

class BanqueController extends Controller
{
    private StructureService $svc;
    public function __construct() { $this->svc = new StructureService(); }

    public function index(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $banques = $this->svc->listerBanques();
        $this->generateCsrf();
        $this->renderInLayout('Structure/banques/index', compact('banques'), 'Banques');
    }
    public function show(array $p=[]): void
    {
        $this->requireRight('structure','consulter');
        $banque = $this->svc->getBanqueModel()->trouverParId((int)$p['id']);
        if (!$banque) { $this->flash('error','Banque introuvable.'); $this->redirect('/structure/banques'); }
        $versements = $this->svc->getBanqueModel()->versements((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/banques/show', compact('banque','versements'), $banque['nom']);
    }
    public function editForm(array $p=[]): void
    {
        $this->requireRight('structure','modifier');
        $banque = $this->svc->getBanqueModel()->trouverParId((int)$p['id']);
        if (!$banque) { $this->flash('error','Banque introuvable.'); $this->redirect('/structure/banques'); }
        $versements = $this->svc->getBanqueModel()->versements((int)$p['id']);
        $this->generateCsrf();
        $this->renderInLayout('Structure/banques/form', compact('banque','versements'), 'Modifier la banque');
    }
    public function creerTraiter(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->verifyCsrf();
        $r = $this->svc->creerBanque($_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Banque créée.':(implode(' ',$r['errors']??[])));
        $this->redirect('/structure/banques');
    }
    public function editTraiter(array $p=[]): void
    {
        $this->requireRight('structure','modifier'); $this->verifyCsrf();
        $r = $this->svc->modifierBanque((int)$p['id'], $_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Banque modifiée.':(implode(' ',$r['errors']??[])));
        $this->redirect('/structure/banques');
    }
    public function versement(array $p=[]): void
    {
        $this->requireRight('structure','creer'); $this->verifyCsrf();
        $r = $this->svc->ajouterVersement((int)$p['id'], $_POST);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Versement enregistré.':(implode(' ',$r['errors']??[])));
        $this->redirect('/structure/banques');
    }
    public function supprimer(array $p=[]): void
    {
        $this->requireRight('structure','supprimer'); $this->verifyCsrf();
        $r = $this->svc->supprimerBanque((int)$p['id']);
        $this->flash($r['ok']?'success':'error', $r['ok']?'Banque supprimée.':($r['message']??'Erreur.'));
        $this->redirect('/structure/banques');
    }
}
