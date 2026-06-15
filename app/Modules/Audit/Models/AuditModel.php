<?php
declare(strict_types=1);
namespace App\Modules\Audit\Models;
use App\Core\Database;

class AuditModel
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);

        return $this->db->fetchAll(
            "SELECT j.id_journal, j.table_cible, j.action, j.id_enregistrement,
                    j.anciennes_valeurs, j.nouvelles_valeurs, j.ip_adresse, j.created_at,
                    u.login AS user_login, u.nom AS user_nom, u.prenom AS user_prenom
             FROM journal_audit j
             LEFT JOIN utilisateur u ON u.id_utilisateur = j.id_utilisateur
             WHERE {$where}
             ORDER BY j.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset])
        );
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM journal_audit j
             LEFT JOIN utilisateur u ON u.id_utilisateur = j.id_utilisateur
             WHERE {$where}",
            $params
        );
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT j.*, u.login AS user_login, u.nom AS user_nom, u.prenom AS user_prenom
             FROM journal_audit j
             LEFT JOIN utilisateur u ON u.id_utilisateur = j.id_utilisateur
             WHERE j.id_journal = :id",
            [':id' => $id]
        );
    }

    /** Listes distinctes pour les filtres */
    public function tablesDistinctes(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT table_cible FROM journal_audit ORDER BY table_cible"
        );
    }

    public function utilisateursDistincts(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT u.id_utilisateur, u.login, u.nom, u.prenom
             FROM journal_audit j
             JOIN utilisateur u ON u.id_utilisateur = j.id_utilisateur
             ORDER BY u.nom"
        );
    }

    /** Stats résumées pour le dashboard audit */
    public function statsResumees(): array
    {
        return $this->db->fetchOne(
            "SELECT
                COUNT(*) AS total,
                COUNT(*) FILTER (WHERE action = 'INSERT')     AS nb_insert,
                COUNT(*) FILTER (WHERE action = 'UPDATE')     AS nb_update,
                COUNT(*) FILTER (WHERE action = 'DELETE')     AS nb_delete,
                COUNT(*) FILTER (WHERE action = 'CONNEXION')  AS nb_connexion,
                COUNT(*) FILTER (WHERE created_at >= NOW() - INTERVAL '24 hours') AS derniere_24h
             FROM journal_audit"
        ) ?: [];
    }

    /** Activité par jour sur les N derniers jours */
    public function activiteParJour(int $jours = 30): array
    {
        return $this->db->fetchAll(
            "SELECT DATE(created_at) AS jour, COUNT(*) AS nb
             FROM journal_audit
             WHERE created_at >= NOW() - INTERVAL '{$jours} days'
             GROUP BY DATE(created_at)
             ORDER BY jour"
        );
    }

    /** Export CSV — retourne les données brutes */
    public function exportCsv(array $filtres): array
    {
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT j.id_journal, j.created_at, u.login AS utilisateur,
                    j.table_cible, j.action, j.id_enregistrement, j.ip_adresse,
                    j.anciennes_valeurs, j.nouvelles_valeurs
             FROM journal_audit j
             LEFT JOIN utilisateur u ON u.id_utilisateur = j.id_utilisateur
             WHERE {$where}
             ORDER BY j.created_at DESC
             LIMIT 10000",
            $params
        );
    }

    /** Purge des entrées plus anciennes que N jours (admin seulement) */
    public function purger(int $joursAvant): int
    {
        return $this->db->execute(
            "DELETE FROM journal_audit WHERE created_at < NOW() - INTERVAL :j",
            [':j' => "{$joursAvant} days"]
        );
    }

    private function buildWhere(array $f): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($f['table'])) {
            $where[]          = 'j.table_cible = :table';
            $params[':table'] = $f['table'];
        }
        if (!empty($f['action'])) {
            $where[]           = "j.action = :action::t_action_audit";
            $params[':action'] = $f['action'];
        }
        if (!empty($f['user_id'])) {
            $where[]          = 'j.id_utilisateur = :uid';
            $params[':uid']   = (int)$f['user_id'];
        }
        if (!empty($f['date_debut'])) {
            $where[]              = 'j.created_at >= :ddebut';
            $params[':ddebut']    = $f['date_debut'] . ' 00:00:00';
        }
        if (!empty($f['date_fin'])) {
            $where[]              = 'j.created_at <= :dfin';
            $params[':dfin']      = $f['date_fin'] . ' 23:59:59';
        }
        if (!empty($f['recherche'])) {
            $where[]              = "(j.table_cible ILIKE :rech OR u.login ILIKE :rech OR j.ip_adresse::text ILIKE :rech)";
            $params[':rech']      = '%' . $f['recherche'] . '%';
        }

        return [implode(' AND ', $where), $params];
    }
}
