<?php
declare(strict_types=1);
namespace App\Modules\Archive\Controllers;

use App\Core\Controller;
use App\Modules\Archive\Services\ArchiveService;

class ArchiveController extends Controller
{
    private ArchiveService $service;

    public function __construct() { $this->service = new ArchiveService(); }

    /** GET /archive */
    public function index(array $p = []): void
    {
        $this->requireAuth();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filtres = [
            'entite'     => $_GET['entite']     ?? '',
            'action'     => $_GET['action']     ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin'   => $_GET['date_fin']   ?? '',
        ];
        $data = $this->service->lister($page, $filtres);
        $this->generateCsrf();
        $this->renderInLayout('Archive/index', $data, 'Corbeille');
    }

    /** GET /archive/:id */
    public function show(array $p = []): void
    {
        $this->requireAuth();
        $archive = $this->service->detail((int)$p['id']);
        if (!$archive) {
            $this->flash('error', 'Archive introuvable.');
            $this->redirect('/archive');
        }
        $this->generateCsrf();
        $this->renderInLayout('Archive/show', compact('archive'), 'Archive #' . $p['id']);
    }

    /** POST /archive/:id/restaurer */
    public function restaurer(array $p = []): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $result = $this->service->restaurer((int)$p['id']);
        $this->flash($result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Élément restauré avec succès.' : ($result['message'] ?? 'Erreur.'));
        $this->redirect('/archive');
    }

    /** POST /archive/:id/supprimer */
    public function supprimerDefinitivement(array $p = []): void
    {
        $this->requireRight('securite', 'supprimer');
        $this->verifyCsrf();
        $result = $this->service->supprimerDefinitivement((int)$p['id']);
        $this->flash($result['ok'] ? 'success' : 'error',
            $result['ok'] ? 'Supprimé définitivement.' : ($result['message'] ?? 'Erreur.'));
        $this->redirect('/archive');
    }

    /** GET /archive/:id/xml — téléchargement du fichier XML */
    public function telechargerXml(array $p = []): void
    {
        $this->requireAuth();
        $result = $this->service->obtenirXml((int)$p['id']);
        if (!$result) {
            $this->flash('error', 'Fichier XML introuvable.');
            $this->redirect('/archive');
        }
        $filename = 'archive_' . $p['id'] . '_' . date('Ymd') . '.xml';
        header('Content-Type: application/xml; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $result['xml'];
        exit;
    }
}
