<?php
declare(strict_types=1);
namespace App\Modules\Structure\Models;
use App\Core\Database;

class FamilleModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page = 1, int $perPage = 20, string $rech = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = "f.deleted_at IS NULL";
        $params = [':limit' => $perPage, ':offset' => $offset];
        if ($rech) { $where .= " AND (f.libelle ILIKE :r OR f.description ILIKE :r)"; $params[':r'] = "%$rech%"; }
        return $this->db->fetchAll(
            "SELECT f.id_famille, f.libelle, f.description, f.created_at,
                    COUNT(p.id_produit) AS nb_produits
             FROM famille_produit f
             LEFT JOIN produit p ON p.id_famille = f.id_famille AND p.deleted_at IS NULL
             WHERE {$where}
             GROUP BY f.id_famille, f.libelle, f.description, f.created_at
             ORDER BY f.libelle LIMIT :limit OFFSET :offset", $params);
    }

    public function compter(string $rech = ''): int
    {
        $where  = "deleted_at IS NULL";
        $params = [];
        if ($rech) { $where .= " AND (libelle ILIKE :r OR description ILIKE :r)"; $params[':r'] = "%$rech%"; }
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM famille_produit WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT id_famille, libelle, description FROM famille_produit WHERE id_famille=:id AND deleted_at IS NULL",
            [':id' => $id]);
    }

    public function toutesOptions(): array
    {
        return $this->db->fetchAll(
            "SELECT id_famille, libelle FROM famille_produit WHERE deleted_at IS NULL ORDER BY libelle");
    }

    public function creer(string $libelle, string $desc): int
    {
        $this->db->execute(
            "INSERT INTO famille_produit(libelle, description) VALUES(:l,:d)",
            [':l' => $libelle, ':d' => $desc]);
        return (int)$this->db->lastInsertId('entite_oid_entite_seq');
    }

    public function modifier(int $id, string $libelle, string $desc): void
    {
        $this->db->execute(
            "UPDATE famille_produit SET libelle=:l, description=:d, updated_at=NOW() WHERE id_famille=:id",
            [':l' => $libelle, ':d' => $desc, ':id' => $id]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE famille_produit SET deleted_at=NOW() WHERE id_famille=:id", [':id' => $id]);
    }

    public function aProduits(int $id): bool
    {
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM produit WHERE id_famille=:id AND deleted_at IS NULL", [':id' => $id]);
        return (int)($r['n'] ?? 0) > 0;
    }

    public function libelleExiste(string $libelle, int $excludeId = 0): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM famille_produit WHERE libelle=:l AND deleted_at IS NULL AND id_famille<>:ex",
            [':l' => $libelle, ':ex' => $excludeId]);
        return (int)($r['n'] ?? 0) > 0;
    }
}
