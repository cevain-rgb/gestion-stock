<?php
declare(strict_types=1);
namespace App\Modules\Archive\Models;
use App\Core\Database;

class ArchiveModel
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);

        return $this->db->fetchAll(
            "SELECT a.id_archive, a.entite, a.id_entite, a.action, a.created_at,
                    u.login AS user_login, u.nom AS user_nom, u.prenom AS user_prenom,
                    LEFT(a.xml_data, 400) AS xml_preview
             FROM archive_xml a
             LEFT JOIN utilisateur u ON u.id_utilisateur = a.id_utilisateur
             WHERE {$where}
             ORDER BY a.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset])
        );
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM archive_xml a WHERE {$where}", $params
        );
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT a.*, u.login AS user_login
             FROM archive_xml a
             LEFT JOIN utilisateur u ON u.id_utilisateur = a.id_utilisateur
             WHERE a.id_archive = :id",
            [':id' => $id]
        );
    }

    public function entitesDistinctes(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT entite, COUNT(*) AS nb FROM archive_xml GROUP BY entite ORDER BY entite"
        );
    }

    public function statsParEntite(): array
    {
        return $this->db->fetchAll(
            "SELECT entite,
                    COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE action = 'suppression')  AS nb_suppression,
                    COUNT(*) FILTER (WHERE action = 'restauration') AS nb_restauration
             FROM archive_xml GROUP BY entite ORDER BY total DESC"
        );
    }

    /** Restaure : remet deleted_at = NULL dans la table cible */
    public function restaurer(int $archiveId): bool
    {
        $archive = $this->trouverParId($archiveId);
        if (!$archive) return false;

        $entite = $archive['entite'];
        $idEnt  = (int)$archive['id_entite'];

        // Tables supportant le soft-delete via la colonne deleted_at
        $tablesAutorisees = [
            'produit','famille_produit','fournisseur','client',
            'utilisateur','groupe_utilisateur',
            'commande_fournisseur','commande_client',
            'bon_reception','bon_livraison',
            'facture_fournisseur','facture_client',
            'bon_sortie','don',
        ];

        if (!in_array($entite, $tablesAutorisees, true)) return false;

        // Vérifier que la colonne deleted_at existe et que l'enregistrement est bien soft-deleted
        $row = $this->db->fetchOne(
            "SELECT deleted_at FROM {$entite} WHERE oid_entite = :id OR id_{($entite == 'groupe_utilisateur')?'groupe':$entite} = :id2 LIMIT 1",
            [':id' => $idEnt, ':id2' => $idEnt]
        );

        if (!$row || $row['deleted_at'] === null) return false;

        // Restaurer
        $this->db->execute(
            "UPDATE {$entite} SET deleted_at = NULL, updated_at = NOW()
             WHERE (oid_entite = :id OR id_{($entite=='groupe_utilisateur')?'groupe':$entite} = :id2) AND deleted_at IS NOT NULL",
            [':id' => $idEnt, ':id2' => $idEnt]
        );

        // Marquer l'archive comme restauration
        $this->db->execute(
            "UPDATE archive_xml SET action = 'restauration' WHERE id_archive = :aid",
            [':aid' => $archiveId]
        );

        return true;
    }

    /** Suppression définitive : supprime l'enregistrement physiquement */
    public function supprimerDefinitivement(int $archiveId): bool
    {
        $archive = $this->trouverParId($archiveId);
        if (!$archive) return false;

        $entite = $archive['entite'];
        $idEnt  = (int)$archive['id_entite'];

        $tablesAutorisees = [
            'produit','famille_produit','fournisseur','client',
            'utilisateur','groupe_utilisateur',
        ];

        if (in_array($entite, $tablesAutorisees, true)) {
            try {
                $this->db->execute(
                    "DELETE FROM {$entite} WHERE (oid_entite = :id OR id_{$entite} = :id2) AND deleted_at IS NOT NULL",
                    [':id' => $idEnt, ':id2' => $idEnt]
                );
            } catch (\Throwable) {
                // Contrainte FK : on ne supprime pas physiquement mais on garde l'archive
            }
        }

        // Supprimer l'entrée archive (le XML disque est conservé)
        $this->db->execute("DELETE FROM archive_xml WHERE id_archive = :id", [':id' => $archiveId]);
        return true;
    }

    private function buildWhere(array $f): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($f['entite'])) {
            $where[]           = 'a.entite = :entite';
            $params[':entite'] = $f['entite'];
        }
        if (!empty($f['action'])) {
            $where[]           = 'a.action = :action';
            $params[':action'] = $f['action'];
        }
        if (!empty($f['date_debut'])) {
            $where[]              = 'a.created_at >= :ddebut';
            $params[':ddebut']    = $f['date_debut'] . ' 00:00:00';
        }
        if (!empty($f['date_fin'])) {
            $where[]              = 'a.created_at <= :dfin';
            $params[':dfin']      = $f['date_fin'] . ' 23:59:59';
        }
        return [implode(' AND ', $where), $params];
    }
}
