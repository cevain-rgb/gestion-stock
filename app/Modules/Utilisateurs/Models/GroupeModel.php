<?php
declare(strict_types=1);
namespace App\Modules\Utilisateurs\Models;
use App\Core\Database;

class GroupeModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->db->fetchAll(
            "SELECT g.id_groupe, g.libelle, g.description, g.created_at,
                    COUNT(u.id_utilisateur) AS nb_utilisateurs
             FROM groupe_utilisateur g
             LEFT JOIN utilisateur u ON u.id_groupe = g.id_groupe AND u.deleted_at IS NULL
             WHERE g.deleted_at IS NULL
             GROUP BY g.id_groupe, g.libelle, g.description, g.created_at
             ORDER BY g.libelle
             LIMIT :limit OFFSET :offset",
            [':limit' => $perPage, ':offset' => $offset]
        );
    }

    public function compter(): int
    {
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM groupe_utilisateur WHERE deleted_at IS NULL");
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT id_groupe, libelle, description FROM groupe_utilisateur WHERE id_groupe = :id AND deleted_at IS NULL",
            [':id' => $id]
        );
    }

    public function creer(string $libelle, string $description): int
    {
        $this->db->execute(
            "INSERT INTO groupe_utilisateur(libelle, description) VALUES(:l, :d)",
            [':l' => $libelle, ':d' => $description]
        );
        return (int)$this->db->lastInsertId('entite_oid_entite_seq');
    }

    public function modifier(int $id, string $libelle, string $description): void
    {
        $this->db->execute(
            "UPDATE groupe_utilisateur SET libelle=:l, description=:d, updated_at=NOW() WHERE id_groupe=:id",
            [':l' => $libelle, ':d' => $description, ':id' => $id]
        );
    }

    public function supprimer(int $id): void
    {
        $this->db->execute(
            "UPDATE groupe_utilisateur SET deleted_at=NOW() WHERE id_groupe=:id",
            [':id' => $id]
        );
    }

    public function aDesUtilisateurs(int $id): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM utilisateur WHERE id_groupe=:id AND deleted_at IS NULL",
            [':id' => $id]
        );
        return ((int)($r['n'] ?? 0)) > 0;
    }

    public function libelleExiste(string $libelle, int $excludeId = 0): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM groupe_utilisateur WHERE libelle=:l AND deleted_at IS NULL AND id_groupe <> :ex",
            [':l' => $libelle, ':ex' => $excludeId]
        );
        return ((int)($r['n'] ?? 0)) > 0;
    }
}
