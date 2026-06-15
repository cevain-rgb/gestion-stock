<?php
declare(strict_types=1);
namespace App\Modules\Archive\Services;

use App\Modules\Archive\Models\ArchiveModel;
use App\Core\Database;

class ArchiveService
{
    private ArchiveModel $model;
    private const PER_PAGE = 20;

    public function __construct() { $this->model = new ArchiveModel(); }

    public function lister(int $page, array $filtres): array
    {
        $total = $this->model->compter($filtres);
        return [
            'lignes'     => $this->model->tous($page, self::PER_PAGE, $filtres),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => self::PER_PAGE,
            'totalPages' => max(1, (int)ceil($total / self::PER_PAGE)),
            'filtres'    => $filtres,
            'entites'    => $this->model->entitesDistinctes(),
            'stats'      => $this->model->statsParEntite(),
        ];
    }

    public function detail(int $id): array|false
    {
        $archive = $this->model->trouverParId($id);
        if (!$archive) return false;

        // Parser le XML pour affichage structuré
        $archive['champs'] = $this->parseXml($archive['xml_data']);
        return $archive;
    }

    public function restaurer(int $id): array
    {
        $archive = $this->model->trouverParId($id);
        if (!$archive) return ['ok' => false, 'message' => 'Archive introuvable.'];
        if ($archive['action'] === 'restauration') {
            return ['ok' => false, 'message' => 'Cet élément a déjà été restauré.'];
        }

        $ok = $this->model->restaurer($id);
        if (!$ok) {
            return ['ok' => false, 'message' => 'Restauration impossible (contrainte ou table non supportée).'];
        }

        $this->journaliser('UPDATE', $archive['entite'], (int)$archive['id_entite'],
            ['action' => 'suppression'], ['action' => 'restauration']);
        return ['ok' => true];
    }

    public function supprimerDefinitivement(int $id): array
    {
        $archive = $this->model->trouverParId($id);
        if (!$archive) return ['ok' => false, 'message' => 'Archive introuvable.'];

        $ok = $this->model->supprimerDefinitivement($id);
        if (!$ok) return ['ok' => false, 'message' => 'Suppression impossible.'];

        $this->journaliser('DELETE', $archive['entite'], (int)$archive['id_entite'],
            ['action' => 'suppression_definitive'], null);
        return ['ok' => true];
    }

    /** Télécharge le fichier XML d'archive depuis le disque ou la BDD */
    public function obtenirXml(int $id): array|false
    {
        $archive = $this->model->trouverParId($id);
        if (!$archive) return false;

        // Chercher d'abord sur disque
        $dir  = BASE_PATH . '/storage/archives/' . $archive['entite'];
        $glob = glob($dir . '/' . $archive['id_entite'] . '_*.xml');
        if ($glob) {
            return ['xml' => file_get_contents(end($glob)), 'source' => 'disk'];
        }
        // Fallback BDD
        return ['xml' => $archive['xml_data'], 'source' => 'db'];
    }

    private function parseXml(string $xmlString): array
    {
        $champs = [];
        try {
            $xml = new \SimpleXMLElement($xmlString);
            foreach ($xml->champ as $champ) {
                $champs[(string)$champ['nom']] = (string)$champ;
            }
        } catch (\Throwable) { /* XML malformé */ }
        return $champs;
    }

    private function journaliser(string $action, string $table, int $id, mixed $old, mixed $new): void
    {
        Database::getInstance()->execute(
            "INSERT INTO journal_audit(id_utilisateur, table_cible, action, id_enregistrement, anciennes_valeurs, nouvelles_valeurs, ip_adresse)
             VALUES(:u, :t, :a::t_action_audit, :id, :old::jsonb, :new::jsonb, :ip::inet)",
            [
                ':u'   => $_SESSION['user_id'] ?? null,
                ':t'   => $table,
                ':a'   => $action,
                ':id'  => $id,
                ':old' => $old ? json_encode($old) : null,
                ':new' => $new ? json_encode($new) : null,
                ':ip'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]
        );
    }
}
