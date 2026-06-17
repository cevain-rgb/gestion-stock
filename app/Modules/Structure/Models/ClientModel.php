<?php
declare(strict_types=1);
namespace App\Modules\Structure\Models;
use App\Core\Database;

class ClientModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ["c.deleted_at IS NULL"];
        $params = [':limit' => $perPage, ':offset' => $offset];
        if (!empty($filtres['recherche'])) {
            $where[] = "(c.nom ILIKE :r OR (c.contact).email ILIKE :r OR (c.contact).telephone ILIKE :r)";
            $params[':r'] = '%' . $filtres['recherche'] . '%';
        }
        if (!empty($filtres['id_categorie'])) {
            $where[] = "c.id_categorie=:cat";
            $params[':cat'] = (int)$filtres['id_categorie'];
        }
        return $this->db->fetchAll(
            "SELECT c.id_client, c.nom, c.created_at,
                    (c.contact).telephone AS telephone, (c.contact).email AS email,
                    ((c.contact).adresse).ville AS ville,
                    cc.libelle AS categorie, cc.remise_pct,
                    COUNT(DISTINCT cmd.oid_doc) AS nb_commandes,
                    COALESCE(SUM(cmd.montant_total),0) AS total_achats
             FROM client c
             JOIN categorie_client cc ON cc.id_categorie = c.id_categorie
             LEFT JOIN commande_client cmd ON cmd.id_client = c.id_client AND cmd.deleted_at IS NULL AND cmd.statut <> 'annulee'
             WHERE " . implode(' AND ', $where) . "
             GROUP BY c.id_client, c.nom, c.created_at, c.contact, cc.libelle, cc.remise_pct
             ORDER BY c.nom LIMIT :limit OFFSET :offset", $params);
    }

    public function compter(array $filtres = []): int
    {
        $where  = ["c.deleted_at IS NULL"];
        $params = [];
        if (!empty($filtres['recherche'])) { $where[] = "c.nom ILIKE :r"; $params[':r'] = '%'.$filtres['recherche'].'%'; }
        if (!empty($filtres['id_categorie'])) { $where[] = "c.id_categorie=:cat"; $params[':cat'] = (int)$filtres['id_categorie']; }
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM client c WHERE " . implode(' AND ', $where), $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT c.*, cc.libelle AS categorie_libelle, cc.remise_pct,
                    (c.contact).telephone AS telephone, (c.contact).email AS email,
                    ((c.contact).adresse).rue       AS rue,
                    ((c.contact).adresse).ville     AS ville,
                    ((c.contact).adresse).code_postal AS code_postal,
                    ((c.contact).adresse).pays      AS pays
             FROM client c JOIN categorie_client cc ON cc.id_categorie=c.id_categorie
             WHERE c.id_client=:id AND c.deleted_at IS NULL", [':id' => $id]);
    }

    public function tousOptions(): array
    {
        return $this->db->fetchAll(
            "SELECT c.id_client, c.nom, cc.remise_pct
             FROM client c JOIN categorie_client cc ON cc.id_categorie=c.id_categorie
             WHERE c.deleted_at IS NULL ORDER BY c.nom");
    }

    public function creer(array $d): int
    {
        $this->db->execute(
            "INSERT INTO client(id_categorie, nom, contact) VALUES(:cat,:n,
             ROW(:tel,:email,ROW(:rue,:ville,:cp,:pays)::t_adresse)::t_contact)",
            $this->bind($d));
        return (int)$this->db->lastInsertId('entite_oid_entite_seq');
    }

    public function modifier(int $id, array $d): void
    {
        $p = $this->bind($d); $p[':id'] = $id;
        $this->db->execute(
            "UPDATE client SET id_categorie=:cat, nom=:n, updated_at=NOW(),
             contact=ROW(:tel,:email,ROW(:rue,:ville,:cp,:pays)::t_adresse)::t_contact
             WHERE id_client=:id", $p);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE client SET deleted_at=NOW() WHERE id_client=:id", [':id' => $id]);
    }

    public function dernieresCommandes(int $idClient, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT oid_doc, numero, date_document, statut, montant_total
             FROM commande_client
             WHERE id_client=:id AND deleted_at IS NULL
             ORDER BY date_document DESC LIMIT :lim",
            [':id' => $idClient, ':lim' => $limit]);
    }

    public function soldeDu(int $idClient): float
    {
        $r = $this->db->fetchOne(
            "SELECT COALESCE(SUM(v.reste_a_payer),0) AS solde
             FROM v_factures_c_impayees v
             JOIN commande_client cc ON cc.oid_doc = v.oid_doc
             WHERE cc.id_client = :id",
            [':id' => $idClient]);
        return (float)($r['solde'] ?? 0);
    }

    private function bind(array $d): array
    {
        return [
            ':cat'   => (int)$d['id_categorie'],
            ':n'     => trim($d['nom']),
            ':tel'   => trim($d['telephone'] ?? ''),
            ':email' => trim($d['email'] ?? ''),
            ':rue'   => trim($d['rue'] ?? ''),
            ':ville' => trim($d['ville'] ?? ''),
            ':cp'    => trim($d['code_postal'] ?? ''),
            ':pays'  => trim($d['pays'] ?? ''),
        ];
    }
}
