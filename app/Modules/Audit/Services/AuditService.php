<?php
declare(strict_types=1);
namespace App\Modules\Audit\Services;
use App\Modules\Audit\Models\AuditModel;

class AuditService
{
    private AuditModel $model;
    private const PER_PAGE = 25;

    public function __construct() { $this->model = new AuditModel(); }

    public function lister(int $page, array $filtres): array
    {
        $total = $this->model->compter($filtres);
        return [
            'lignes'      => $this->model->tous($page, self::PER_PAGE, $filtres),
            'total'       => $total,
            'page'        => $page,
            'perPage'     => self::PER_PAGE,
            'totalPages'  => max(1, (int)ceil($total / self::PER_PAGE)),
            'filtres'     => $filtres,
            'tables'      => $this->model->tablesDistinctes(),
            'utilisateurs'=> $this->model->utilisateursDistincts(),
            'stats'       => $this->model->statsResumees(),
        ];
    }

    public function detail(int $id): array|false
    {
        return $this->model->trouverParId($id);
    }

    public function activiteGraphique(): array
    {
        $rows   = $this->model->activiteParJour(30);
        $labels = [];
        $values = [];
        // Remplir les 30 derniers jours même si 0 événement
        for ($i = 29; $i >= 0; $i--) {
            $date = (new \DateTime("-{$i} days"))->format('Y-m-d');
            $labels[] = (new \DateTime($date))->format('d/m');
            $values[] = 0;
        }
        foreach ($rows as $r) {
            $idx = array_search((new \DateTime($r['jour']))->format('d/m'), $labels);
            if ($idx !== false) $values[$idx] = (int)$r['nb'];
        }
        return compact('labels', 'values');
    }

    public function genererCsv(array $filtres): string
    {
        $rows = $this->model->exportCsv($filtres);
        $cols = ['id_journal','created_at','utilisateur','table_cible','action','id_enregistrement','ip_adresse','anciennes_valeurs','nouvelles_valeurs'];

        $out  = implode(';', $cols) . "\n";
        foreach ($rows as $row) {
            $line = [];
            foreach ($cols as $c) {
                $val = $row[$c] ?? '';
                // Échapper les guillemets et emballer dans guillemets si besoin
                if (str_contains((string)$val, ';') || str_contains((string)$val, '"') || str_contains((string)$val, "\n")) {
                    $val = '"' . str_replace('"', '""', $val) . '"';
                }
                $line[] = $val;
            }
            $out .= implode(';', $line) . "\n";
        }
        return $out;
    }

    public function purger(int $joursAvant): int
    {
        return $this->model->purger($joursAvant);
    }
}
