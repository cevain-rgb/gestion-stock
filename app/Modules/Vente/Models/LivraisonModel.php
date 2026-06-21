<?php
declare(strict_types=1);
namespace App\Modules\Vente\Models;
use App\Core\Database;

class LivraisonModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT bl.oid_doc, bl.numero, bl.date_document, bl.id_commande_c,
                    cc.numero AS numero_commande, c.nom AS client,
                    COALESCE(SUM(ll.quantite_livree * ll.prix_unitaire),0) AS montant_livre
             FROM bon_livraison bl
             JOIN commande_client cc ON cc.oid_doc = bl.id_commande_c
             JOIN client c ON c.id_client = cc.id_client
             LEFT JOIN ligne_livraison ll ON ll.id_livraison = bl.oid_doc
             WHERE {$where}
             GROUP BY bl.oid_doc, bl.numero, bl.date_document, bl.id_commande_c, cc.numero, c.nom
             ORDER BY bl.date_document DESC, bl.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM bon_livraison bl
             JOIN commande_client cc ON cc.oid_doc=bl.id_commande_c
             JOIN client c ON c.id_client=cc.id_client WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT bl.*, cc.numero AS numero_commande, c.nom AS client, u.login AS cree_par
             FROM bon_livraison bl
             JOIN commande_client cc ON cc.oid_doc = bl.id_commande_c
             JOIN client c ON c.id_client = cc.id_client
             JOIN utilisateur u ON u.id_utilisateur = bl.id_utilisateur
             WHERE bl.oid_doc=:id AND bl.deleted_at IS NULL", [':id' => $id]);
    }

    public function lignes(int $idLivraison): array
    {
        return $this->db->fetchAll(
            "SELECT ll.id_ligne_l, ll.id_produit, ll.quantite_livree, ll.prix_unitaire,
                    (ll.quantite_livree * ll.prix_unitaire) AS montant_ligne,
                    p.code, p.designation, p.unite
             FROM ligne_livraison ll
             JOIN produit p ON p.id_produit = ll.id_produit
             WHERE ll.id_livraison=:id ORDER BY ll.id_ligne_l", [':id' => $idLivraison]);
    }

    public function quantitesLivreesParCommande(int $idCommande): array
    {
        $rows = $this->db->fetchAll(
            "SELECT ll.id_produit, SUM(ll.quantite_livree) AS qte_livree
             FROM ligne_livraison ll
             JOIN bon_livraison bl ON bl.oid_doc = ll.id_livraison
             WHERE bl.id_commande_c=:id AND bl.deleted_at IS NULL
             GROUP BY ll.id_produit", [':id' => $idCommande]);
        return array_column($rows, 'qte_livree', 'id_produit');
    }

    public function creer(int $idCommande, int $idUtilisateur, string $observations): int
    {
        $this->db->execute(
            "INSERT INTO bon_livraison(id_commande_c, id_utilisateur, observations, date_document)
             VALUES(:c,:u,:obs,CURRENT_DATE)",
            [':c' => $idCommande, ':u' => $idUtilisateur, ':obs' => $observations]);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM bon_livraison WHERE id_commande_c=:c AND id_utilisateur=:u",
            [':c' => $idCommande, ':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    /** L'insertion déclenche le trigger trg_livraison_stock (BEFORE INSERT) qui décrémente le stock,
     *  et lève une exception PostgreSQL si le stock est insuffisant. */
    public function ajouterLigne(int $idLivraison, int $idProduit, float $qte, float $prixUnit): void
    {
        $this->db->execute(
            "INSERT INTO ligne_livraison(id_livraison, id_produit, quantite_livree, prix_unitaire)
             VALUES(:l,:p,:q,:pu)",
            [':l' => $idLivraison, ':p' => $idProduit, ':q' => $qte, ':pu' => $prixUnit]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE bon_livraison SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }

    private function buildWhere(array $f): array
    {
        $where  = ['bl.deleted_at IS NULL'];
        $params = [];
        if (!empty($f['recherche'])) { $where[]="(bl.numero ILIKE :r OR c.nom ILIKE :r OR cc.numero ILIKE :r)"; $params[':r']='%'.$f['recherche'].'%'; }
        if (!empty($f['date_debut'])){ $where[]="bl.date_document>=:dd"; $params[':dd']=$f['date_debut']; }
        if (!empty($f['date_fin']))  { $where[]="bl.date_document<=:df"; $params[':df']=$f['date_fin']; }
        return [implode(' AND ', $where), $params];
    }
}
