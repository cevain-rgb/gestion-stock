<?php
declare(strict_types=1);
namespace App\Modules\Audit\Controllers;

use App\Core\Controller;
use App\Modules\Audit\Services\AuditService;

class AuditController extends Controller
{
    private AuditService $service;

    public function __construct() { $this->service = new AuditService(); }

    /** GET /audit */
    public function index(array $p = []): void
    {
        $this->requireAuth();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filtres = [
            'table'      => $_GET['table']      ?? '',
            'action'     => $_GET['action']     ?? '',
            'user_id'    => $_GET['user_id']    ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin'   => $_GET['date_fin']   ?? '',
            'recherche'  => $_GET['q']          ?? '',
        ];

        $data    = $this->service->lister($page, $filtres);
        $graphe  = $this->service->activiteGraphique();

        $this->renderInLayout('Audit/index', array_merge($data, ['graphe' => $graphe]), 'Journal d\'audit');
    }

    /** GET /audit/:id */
    public function show(array $p = []): void
    {
        $this->requireAuth();
        $entree = $this->service->detail((int)$p['id']);
        if (!$entree) {
            $this->flash('error', 'Entrée introuvable.');
            $this->redirect('/audit');
        }
        $this->renderInLayout('Audit/show', compact('entree'), 'Détail — Audit #' . $p['id']);
    }

    /** GET /audit/export */
    public function export(array $p = []): void
    {
        $this->requireAuth();
        $filtres = [
            'table'      => $_GET['table']      ?? '',
            'action'     => $_GET['action']     ?? '',
            'user_id'    => $_GET['user_id']    ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin'   => $_GET['date_fin']   ?? '',
            'recherche'  => $_GET['q']          ?? '',
        ];

        $csv      = $this->service->genererCsv($filtres);
        $filename = 'audit_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen("\xEF\xBB\xBF" . $csv)); // BOM UTF-8 pour Excel
        echo "\xEF\xBB\xBF" . $csv;
        exit;
    }

    /** POST /audit/purger (admin seulement) */
    public function purger(array $p = []): void
    {
        $this->requireRight('securite', 'supprimer');
        $this->verifyCsrf();
        $jours = max(30, (int)($_POST['jours'] ?? 90));
        $nb    = $this->service->purger($jours);
        $this->flash('success', "{$nb} entrée(s) supprimée(s) (antérieures à {$jours} jours).");
        $this->redirect('/audit');
    }
}
