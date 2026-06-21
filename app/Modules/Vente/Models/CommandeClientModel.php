<?php
declare(strict_types=1);
namespace App\Modules\Vente\Models;
use App\Core\Database;

class CommandeClientModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT cc.oid_doc, cc.numero, cc.date_document, cc.statut, cc.montant_total,
                    cc.est_comptant, cc.observations, c.nom AS client, c.id_client,
                    u.login AS cree_par
             FROM commande_client cc
             JOIN client c ON c.id_client = cc.id_client
             JOIN utilisateur u ON u.id_utilisateur = cc.id_utilisateur
             WHERE {$where}
             ORDER BY cc.date_document DESC, cc.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM commande_client cc
             JOIN client c ON c.id_client=cc.id_client WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT cc.*, c.nom AS client, c.id_categorie, u.login AS cree_par
             FROM commande_client cc
             JOIN client c ON c.id_client = cc.id_client
             JOIN utilisateur u ON u.id_utilisateur = cc.id_utilisateur
             WHERE cc.oid_doc=:id AND cc.deleted_at IS NULL", [':id' => $id]);
    }

    public function lignes(int $idCommande): array
    {
        return $this->db->fetchAll(
            "SELECT l.id_ligne_c, l.id_produit, l.quantite, l.prix_unitaire, l.remise_pct, l.montant_ligne,
                    p.code, p.designation, p.unite, p.stock_actuel
             FROM ligne_commande_c l
             JOIN produit p ON p.id_produit = l.id_produit
             WHERE l.id_commande_c=:id ORDER BY l.id_ligne_c", [':id' => $idCommande]);
    }

    public function creer(int $idClient, int $idUtilisateur, string $observations, bool $estComptant = false): int
    {
        $table = $estComptant ? 'vente_comptant' : 'commande_client';
        $this->db->execute(
            "INSERT INTO {$table}(id_client, id_utilisateur, observations, date_document, est_comptant)
             VALUES(:c,:u,:obs,CURRENT_DATE,:comptant)",
            [':c' => $idClient, ':u' => $idUtilisateur, ':obs' => $observations, ':comptant' => $estComptant ? 'TRUE' : 'FALSE']);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM commande_client WHERE id_client=:c AND id_utilisateur=:u",
            [':c' => $idClient, ':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    public function ajouterLigne(int $idCommande, int $idProduit, float $qte, float $prixUnit, float $remisePct = 0): void
    {
        $this->db->execute(
            "INSERT INTO ligne_commande_c(id_commande_c, id_produit, quantite, prix_unitaire, remise_pct)
             VALUES(:c,:p,:q,:pu,:r)",
            [':c' => $idCommande, ':p' => $idProduit, ':q' => $qte, ':pu' => $prixUnit, ':r' => $remisePct]);
    }

    public function viderLignes(int $idCommande): void
    {
        $this->db->execute("DELETE FROM ligne_commande_c WHERE id_commande_c=:id", [':id' => $idCommande]);
    }

    public function changerStatut(int $id, string $statut): void
    {
        $this->db->execute(
            "UPDATE commande_client SET statut=:s::t_statut_commande_c, updated_at=NOW() WHERE oid_doc=:id",
            [':s' => $statut, ':id' => $id]);
    }

    public function modifierObservations(int $id, string $obs): void
    {
        $this->db->execute(
            "UPDATE commande_client SET observations=:o, updated_at=NOW() WHERE oid_doc=:id",
            [':o' => $obs, ':id' => $id]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE commande_client SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }

    public function aDesLivraisons(int $id): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM bon_livraison WHERE id_commande_c=:id AND deleted_at IS NULL", [':id' => $id]);
        return (int)($r['n'] ?? 0) > 0;
    }

    public function nbLignes(int $id): int
    {
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM ligne_commande_c WHERE id_commande_c=:id", [':id' => $id]);
        return (int)($r['n'] ?? 0);
    }

    public function statsResumees(): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE statut='en_attente') AS en_attente,
                    COUNT(*) FILTER (WHERE statut='validee')    AS validees,
                    COUNT(*) FILTER (WHERE statut='livree')     AS livrees,
                    COALESCE(SUM(montant_total) FILTER (WHERE statut<>'annulee'),0) AS montant_total
             FROM commande_client WHERE deleted_at IS NULL AND est_comptant = FALSE") ?: [];
    }

    private function buildWhere(array $f): array
    {
        $where  = ['cc.deleted_at IS NULL'];
        $params = [];
        if (!isset($f['comptant'])) { $where[] = 'cc.est_comptant = FALSE'; }
        elseif ($f['comptant'] === '1') { $where[] = 'cc.est_comptant = TRUE'; }
        if (!empty($f['recherche'])) { $where[]="(cc.numero ILIKE :r OR c.nom ILIKE :r)"; $params[':r']='%'.$f['recherche'].'%'; }
        if (!empty($f['statut']))    { $where[]="cc.statut=:s::t_statut_commande_c"; $params[':s']=$f['statut']; }
        if (!empty($f['id_client'])) { $where[]="cc.id_client=:cid"; $params[':cid']=(int)$f['id_client']; }
        if (!empty($f['date_debut'])){ $where[]="cc.date_document>=:dd"; $params[':dd']=$f['date_debut']; }
        if (!empty($f['date_fin']))  { $where[]="cc.date_document<=:df"; $params[':df']=$f['date_fin']; }
        return [implode(' AND ', $where), $params];
    }
}
