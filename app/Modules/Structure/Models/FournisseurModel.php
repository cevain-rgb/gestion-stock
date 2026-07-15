<?php
declare(strict_types=1);
namespace App\Modules\Structure\Models;
use App\Core\Database;

class FournisseurModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, string $rech = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = "f.deleted_at IS NULL";
        $params = [':limit' => $perPage, ':offset' => $offset];
        if ($rech) { $where .= " AND (f.nom ILIKE :r OR (f.contact).email ILIKE :r OR (f.contact).telephone ILIKE :r)"; $params[':r'] = "%$rech%"; }
        return $this->db->fetchAll(
            "SELECT f.id_fournisseur, f.nom, f.created_at,
                    (f.contact).telephone AS telephone,
                    (f.contact).email     AS email,
                    ((f.contact).adresse).ville AS ville,
                    COUNT(DISTINCT cf.oid_doc) AS nb_commandes
             FROM fournisseur f
             LEFT JOIN commande_fournisseur cf ON cf.id_fournisseur = f.id_fournisseur AND cf.deleted_at IS NULL
             WHERE {$where}
             GROUP BY f.id_fournisseur, f.nom, f.created_at, f.contact
             ORDER BY f.nom LIMIT :limit OFFSET :offset", $params);
    }

    public function compter(string $rech = ''): int
    {
        $where  = "deleted_at IS NULL";
        $params = [];
        if ($rech) { $where .= " AND (nom ILIKE :r OR (contact).email ILIKE :r)"; $params[':r'] = "%$rech%"; }
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM fournisseur WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT f.id_fournisseur, f.nom,
                    (f.contact).telephone AS telephone, (f.contact).email AS email,
                    ((f.contact).adresse).rue       AS rue,
                    ((f.contact).adresse).ville     AS ville,
                    ((f.contact).adresse).code_postal AS code_postal,
                    ((f.contact).adresse).pays      AS pays
             FROM fournisseur f WHERE id_fournisseur=:id AND deleted_at IS NULL",
            [':id' => $id]);
    }

    public function tousOptions(): array
    {
        return $this->db->fetchAll(
            "SELECT id_fournisseur, nom FROM fournisseur WHERE deleted_at IS NULL ORDER BY nom");
    }

    public function creer(array $d): int
    {
        $this->db->execute(
            "INSERT INTO fournisseur(nom, contact) VALUES(:n,
             ROW(:tel,:email,ROW(:rue,:ville,:cp,:pays)::t_adresse)::t_contact)",
            $this->bindContact($d));
        return (int)$this->db->lastInsertId('entite_oid_entite_seq');
    }

    public function modifier(int $id, array $d): void
    {
        $params       = $this->bindContact($d);
        $params[':id'] = $id;
        $this->db->execute(
            "UPDATE fournisseur SET nom=:n, updated_at=NOW(),
             contact=ROW(:tel,:email,ROW(:rue,:ville,:cp,:pays)::t_adresse)::t_contact
             WHERE id_fournisseur=:id", $params);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE fournisseur SET deleted_at=NOW() WHERE id_fournisseur=:id", [':id' => $id]);
    }

    public function dernieresCommandes(int $idFournisseur, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT oid_doc, numero, date_document, statut, montant_total
             FROM commande_fournisseur
             WHERE id_fournisseur=:id AND deleted_at IS NULL
             ORDER BY date_document DESC LIMIT :lim",
            [':id' => $idFournisseur, ':lim' => $limit]);
    }

    private function bindContact(array $d): array
    {
        return [
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
