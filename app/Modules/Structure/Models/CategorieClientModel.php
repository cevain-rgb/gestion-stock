<?php
declare(strict_types=1);
namespace App\Modules\Structure\Models;
use App\Core\Database;

class CategorieClientModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function toutes(): array
    {
        return $this->db->fetchAll(
            "SELECT cc.id_categorie, cc.libelle, cc.remise_pct,
                    COUNT(c.id_client) AS nb_clients
             FROM categorie_client cc
             LEFT JOIN client c ON c.id_categorie=cc.id_categorie AND c.deleted_at IS NULL
             GROUP BY cc.id_categorie, cc.libelle, cc.remise_pct ORDER BY cc.libelle");
    }

    public function toutesOptions(): array
    {
        return $this->db->fetchAll("SELECT id_categorie, libelle, remise_pct FROM categorie_client ORDER BY libelle");
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne("SELECT * FROM categorie_client WHERE id_categorie=:id", [':id' => $id]);
    }

    public function creer(string $libelle, float $remise): int
    {
        $this->db->execute(
            "INSERT INTO categorie_client(libelle, remise_pct) VALUES(:l,:r)",
            [':l' => $libelle, ':r' => $remise]);
        return (int)$this->db->lastInsertId('categorie_client_id_categorie_seq');
    }

    public function modifier(int $id, string $libelle, float $remise): void
    {
        $this->db->execute(
            "UPDATE categorie_client SET libelle=:l, remise_pct=:r WHERE id_categorie=:id",
            [':l' => $libelle, ':r' => $remise, ':id' => $id]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("DELETE FROM categorie_client WHERE id_categorie=:id", [':id' => $id]);
    }

    public function aClients(int $id): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM client WHERE id_categorie=:id AND deleted_at IS NULL", [':id' => $id]);
        return (int)($r['n'] ?? 0) > 0;
    }
}
