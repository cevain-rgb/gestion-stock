<?php
declare(strict_types=1);
namespace App\Modules\Structure\Models;
use App\Core\Database;

class ProduitModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT p.id_produit, p.code, p.designation, p.unite,
                    p.prix_achat, p.prix_vente, p.stock_actuel, p.stock_alerte,
                    p.is_fractionnaire, p.facteur_fraction, p.id_produit_pere,
                    f.libelle AS famille, f.id_famille,
                    produit_valeur_stock(p.*) AS valeur_stock,
                    produit_est_en_alerte(p.*) AS en_alerte
             FROM produit p
             JOIN famille_produit f ON f.id_famille = p.id_famille
             WHERE {$where}
             ORDER BY p.designation
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM produit p JOIN famille_produit f ON f.id_famille=p.id_famille WHERE {$where}",
            $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT p.*, f.libelle AS famille_libelle,
                    pp.designation AS produit_pere_designation,
                    produit_valeur_stock(p.*) AS valeur_stock,
                    produit_est_en_alerte(p.*) AS en_alerte
             FROM produit p
             JOIN famille_produit f ON f.id_famille = p.id_famille
             LEFT JOIN produit pp ON pp.id_produit = p.id_produit_pere
             WHERE p.id_produit=:id AND p.deleted_at IS NULL",
            [':id' => $id]);
    }

    public function fils(int $idPere): array
    {
        return $this->db->fetchAll(
            "SELECT id_produit, code, designation, stock_actuel, unite, prix_vente
             FROM produit WHERE id_produit_pere=:id AND deleted_at IS NULL ORDER BY designation",
            [':id' => $idPere]);
    }

    public function produitsParents(): array
    {
        return $this->db->fetchAll(
            "SELECT id_produit, code, designation FROM produit
             WHERE id_produit_pere IS NULL AND deleted_at IS NULL ORDER BY designation");
    }

    public function creer(array $d): int
    {
        $this->db->execute(
            "INSERT INTO produit(id_famille, id_produit_pere, code, designation, unite,
                prix_achat, prix_vente, stock_actuel, stock_alerte, is_fractionnaire, facteur_fraction)
             VALUES(:fam,:pere,:code,:des,:unite,:pa,:pv,:sa,:sal,:frac,:ff)",
            [':fam'  => (int)$d['id_famille'],
             ':pere' => !empty($d['id_produit_pere']) ? (int)$d['id_produit_pere'] : null,
             ':code' => trim($d['code']),
             ':des'  => trim($d['designation']),
             ':unite'=> trim($d['unite'] ?? 'unité'),
             ':pa'   => (float)($d['prix_achat'] ?? 0),
             ':pv'   => (float)($d['prix_vente'] ?? 0),
             ':sa'   => (float)($d['stock_actuel'] ?? 0),
             ':sal'  => (float)($d['stock_alerte'] ?? 0),
             ':frac' => !empty($d['is_fractionnaire']) ? 'TRUE' : 'FALSE',
             ':ff'   => !empty($d['facteur_fraction']) ? (float)$d['facteur_fraction'] : 1,
            ]);
        return (int)$this->db->lastInsertId('entite_oid_entite_seq');
    }

    public function modifier(int $id, array $d): void
    {
        $this->db->execute(
            "UPDATE produit SET id_famille=:fam, id_produit_pere=:pere, code=:code,
                designation=:des, unite=:unite, prix_achat=:pa, prix_vente=:pv,
                stock_alerte=:sal, is_fractionnaire=:frac, facteur_fraction=:ff, updated_at=NOW()
             WHERE id_produit=:id",
            [':fam'  => (int)$d['id_famille'],
             ':pere' => !empty($d['id_produit_pere']) ? (int)$d['id_produit_pere'] : null,
             ':code' => trim($d['code']),
             ':des'  => trim($d['designation']),
             ':unite'=> trim($d['unite'] ?? 'unité'),
             ':pa'   => (float)($d['prix_achat'] ?? 0),
             ':pv'   => (float)($d['prix_vente'] ?? 0),
             ':sal'  => (float)($d['stock_alerte'] ?? 0),
             ':frac' => !empty($d['is_fractionnaire']) ? 'TRUE' : 'FALSE',
             ':ff'   => !empty($d['facteur_fraction']) ? (float)$d['facteur_fraction'] : 1,
             ':id'   => $id,
            ]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE produit SET deleted_at=NOW() WHERE id_produit=:id", [':id' => $id]);
    }

    public function codeExiste(string $code, int $excludeId = 0): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM produit WHERE code=:c AND deleted_at IS NULL AND id_produit<>:ex",
            [':c' => $code, ':ex' => $excludeId]);
        return (int)($r['n'] ?? 0) > 0;
    }

    public function statsStock(): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE produit_est_en_alerte(p.*)) AS en_alerte,
                    COALESCE(SUM(produit_valeur_stock(p.*)),0) AS valeur_totale,
                    COUNT(*) FILTER (WHERE stock_actuel = 0) AS en_rupture
             FROM produit p WHERE deleted_at IS NULL") ?: [];
    }

    private function buildWhere(array $f): array
    {
        $where  = ['p.deleted_at IS NULL'];
        $params = [];
        if (!empty($f['recherche'])) {
            $where[]    = "(p.code ILIKE :r OR p.designation ILIKE :r)";
            $params[':r'] = '%' . $f['recherche'] . '%';
        }
        if (!empty($f['id_famille'])) {
            $where[]         = 'p.id_famille=:fam';
            $params[':fam']  = (int)$f['id_famille'];
        }
        if (isset($f['alerte']) && $f['alerte'] === '1') {
            $where[] = 'produit_est_en_alerte(p.*)';
        }
        if (isset($f['rupture']) && $f['rupture'] === '1') {
            $where[] = 'p.stock_actuel = 0';
        }
        return [implode(' AND ', $where), $params];
    }
}
