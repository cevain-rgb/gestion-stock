<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Models;
use App\Core\Database;

class DonModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ['d.deleted_at IS NULL'];
        $params = [];
        if (!empty($filtres['recherche'])) { $where[]="(d.numero ILIKE :r OR f.nom ILIKE :r)"; $params[':r']='%'.$filtres['recherche'].'%'; }
        return $this->db->fetchAll(
            "SELECT d.oid_doc, d.numero, d.date_document, d.observations,
                    f.nom AS fournisseur,
                    COALESCE(SUM(ld.quantite * ld.valeur_unitaire),0) AS valeur_totale
             FROM don d
             LEFT JOIN fournisseur f ON f.id_fournisseur = d.id_fournisseur
             LEFT JOIN ligne_don ld ON ld.id_don = d.oid_doc
             WHERE " . implode(' AND ', $where) . "
             GROUP BY d.oid_doc, d.numero, d.date_document, d.observations, f.nom
             ORDER BY d.date_document DESC, d.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        $where  = ['d.deleted_at IS NULL'];
        $params = [];
        if (!empty($filtres['recherche'])) { $where[]="(d.numero ILIKE :r)"; $params[':r']='%'.$filtres['recherche'].'%'; }
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM don d WHERE " . implode(' AND ', $where), $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT d.*, f.nom AS fournisseur, u.login AS cree_par
             FROM don d
             LEFT JOIN fournisseur f ON f.id_fournisseur = d.id_fournisseur
             JOIN utilisateur u ON u.id_utilisateur = d.id_utilisateur
             WHERE d.oid_doc=:id AND d.deleted_at IS NULL", [':id' => $id]);
    }

    public function lignes(int $idDon): array
    {
        return $this->db->fetchAll(
            "SELECT ld.id_ligne_d, ld.id_produit, ld.quantite, ld.valeur_unitaire,
                    (ld.quantite * ld.valeur_unitaire) AS montant_ligne,
                    p.code, p.designation, p.unite
             FROM ligne_don ld
             JOIN produit p ON p.id_produit = ld.id_produit
             WHERE ld.id_don=:id ORDER BY ld.id_ligne_d", [':id' => $idDon]);
    }

    public function creer(?int $idFournisseur, int $idUtilisateur, string $observations): int
    {
        $this->db->execute(
            "INSERT INTO don(id_fournisseur, id_utilisateur, observations, date_document)
             VALUES(:f,:u,:obs,CURRENT_DATE)",
            [':f' => $idFournisseur, ':u' => $idUtilisateur, ':obs' => $observations]);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM don WHERE id_utilisateur=:u",
            [':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    /** L'insertion déclenche le trigger trg_don_stock qui incrémente le stock automatiquement */
    public function ajouterLigne(int $idDon, int $idProduit, float $qte, float $valeurUnit): void
    {
        $this->db->execute(
            "INSERT INTO ligne_don(id_don, id_produit, quantite, valeur_unitaire)
             VALUES(:d,:p,:q,:vu)",
            [':d' => $idDon, ':p' => $idProduit, ':q' => $qte, ':vu' => $valeurUnit]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE don SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }
}
