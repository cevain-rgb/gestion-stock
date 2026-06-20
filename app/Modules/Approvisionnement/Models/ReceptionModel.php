<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Models;
use App\Core\Database;

class ReceptionModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT br.oid_doc, br.numero, br.date_document, br.id_commande_f,
                    cf.numero AS numero_commande, f.nom AS fournisseur,
                    COALESCE(SUM(lr.quantite_recue * lr.prix_unitaire),0) AS montant_recu
             FROM bon_reception br
             JOIN commande_fournisseur cf ON cf.oid_doc = br.id_commande_f
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             LEFT JOIN ligne_reception lr ON lr.id_reception = br.oid_doc
             WHERE {$where}
             GROUP BY br.oid_doc, br.numero, br.date_document, br.id_commande_f, cf.numero, f.nom
             ORDER BY br.date_document DESC, br.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM bon_reception br
             JOIN commande_fournisseur cf ON cf.oid_doc=br.id_commande_f
             JOIN fournisseur f ON f.id_fournisseur=cf.id_fournisseur WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT br.*, cf.numero AS numero_commande, f.nom AS fournisseur, u.login AS cree_par
             FROM bon_reception br
             JOIN commande_fournisseur cf ON cf.oid_doc = br.id_commande_f
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             JOIN utilisateur u ON u.id_utilisateur = br.id_utilisateur
             WHERE br.oid_doc=:id AND br.deleted_at IS NULL", [':id' => $id]);
    }

    public function lignes(int $idReception): array
    {
        return $this->db->fetchAll(
            "SELECT lr.id_ligne_r, lr.id_produit, lr.quantite_recue, lr.prix_unitaire,
                    (lr.quantite_recue * lr.prix_unitaire) AS montant_ligne,
                    p.code, p.designation, p.unite
             FROM ligne_reception lr
             JOIN produit p ON p.id_produit = lr.id_produit
             WHERE lr.id_reception=:id ORDER BY lr.id_ligne_r", [':id' => $idReception]);
    }

    /** Quantités déjà reçues par produit pour une commande, utile pour ne pas dépasser */
    public function quantitesRecuesParCommande(int $idCommande): array
    {
        $rows = $this->db->fetchAll(
            "SELECT lr.id_produit, SUM(lr.quantite_recue) AS qte_recue
             FROM ligne_reception lr
             JOIN bon_reception br ON br.oid_doc = lr.id_reception
             WHERE br.id_commande_f=:id AND br.deleted_at IS NULL
             GROUP BY lr.id_produit", [':id' => $idCommande]);
        return array_column($rows, 'qte_recue', 'id_produit');
    }

    public function creer(int $idCommande, int $idUtilisateur, string $observations): int
    {
        $this->db->execute(
            "INSERT INTO bon_reception(id_commande_f, id_utilisateur, observations, date_document)
             VALUES(:c,:u,:obs,CURRENT_DATE)",
            [':c' => $idCommande, ':u' => $idUtilisateur, ':obs' => $observations]);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM bon_reception WHERE id_commande_f=:c AND id_utilisateur=:u",
            [':c' => $idCommande, ':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    /** L'insertion déclenche le trigger trg_reception_stock qui incrémente le stock automatiquement */
    public function ajouterLigne(int $idReception, int $idProduit, float $qte, float $prixUnit): void
    {
        $this->db->execute(
            "INSERT INTO ligne_reception(id_reception, id_produit, quantite_recue, prix_unitaire)
             VALUES(:r,:p,:q,:pu)",
            [':r' => $idReception, ':p' => $idProduit, ':q' => $qte, ':pu' => $prixUnit]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE bon_reception SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }

    private function buildWhere(array $f): array
    {
        $where  = ['br.deleted_at IS NULL'];
        $params = [];
        if (!empty($f['recherche'])) { $where[]="(br.numero ILIKE :r OR f.nom ILIKE :r OR cf.numero ILIKE :r)"; $params[':r']='%'.$f['recherche'].'%'; }
        if (!empty($f['date_debut'])){ $where[]="br.date_document>=:dd"; $params[':dd']=$f['date_debut']; }
        if (!empty($f['date_fin']))  { $where[]="br.date_document<=:df"; $params[':df']=$f['date_fin']; }
        return [implode(' AND ', $where), $params];
    }
}
