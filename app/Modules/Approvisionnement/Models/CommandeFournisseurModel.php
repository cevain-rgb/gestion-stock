<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Models;
use App\Core\Database;

class CommandeFournisseurModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT cf.oid_doc, cf.numero, cf.date_document, cf.statut, cf.montant_total,
                    cf.observations, f.nom AS fournisseur, f.id_fournisseur,
                    u.login AS cree_par
             FROM commande_fournisseur cf
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             JOIN utilisateur u ON u.id_utilisateur = cf.id_utilisateur
             WHERE {$where}
             ORDER BY cf.date_document DESC, cf.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM commande_fournisseur cf
             JOIN fournisseur f ON f.id_fournisseur=cf.id_fournisseur WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT cf.*, f.nom AS fournisseur, u.login AS cree_par
             FROM commande_fournisseur cf
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             JOIN utilisateur u ON u.id_utilisateur = cf.id_utilisateur
             WHERE cf.oid_doc=:id AND cf.deleted_at IS NULL", [':id' => $id]);
    }

    public function lignes(int $idCommande): array
    {
        return $this->db->fetchAll(
            "SELECT l.id_ligne_f, l.id_produit, l.quantite, l.prix_unitaire, l.montant_ligne,
                    p.code, p.designation, p.unite
             FROM ligne_commande_f l
             JOIN produit p ON p.id_produit = l.id_produit
             WHERE l.id_commande_f=:id ORDER BY l.id_ligne_f", [':id' => $idCommande]);
    }

    public function creer(int $idFournisseur, int $idUtilisateur, string $observations): int
    {
        $this->db->execute(
            "INSERT INTO commande_fournisseur(id_fournisseur, id_utilisateur, observations, date_document)
             VALUES(:f,:u,:obs,CURRENT_DATE)",
            [':f' => $idFournisseur, ':u' => $idUtilisateur, ':obs' => $observations]);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM commande_fournisseur WHERE id_fournisseur=:f AND id_utilisateur=:u",
            [':f' => $idFournisseur, ':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    public function ajouterLigne(int $idCommande, int $idProduit, float $qte, float $prixUnit): void
    {
        $this->db->execute(
            "INSERT INTO ligne_commande_f(id_commande_f, id_produit, quantite, prix_unitaire)
             VALUES(:c,:p,:q,:pu)",
            [':c' => $idCommande, ':p' => $idProduit, ':q' => $qte, ':pu' => $prixUnit]);
    }

    public function supprimerLigne(int $idLigne): void
    {
        $this->db->execute("DELETE FROM ligne_commande_f WHERE id_ligne_f=:id", [':id' => $idLigne]);
    }

    public function viderLignes(int $idCommande): void
    {
        $this->db->execute("DELETE FROM ligne_commande_f WHERE id_commande_f=:id", [':id' => $idCommande]);
    }

    public function changerStatut(int $id, string $statut): void
    {
        $this->db->execute(
            "UPDATE commande_fournisseur SET statut=:s::t_statut_commande_f, updated_at=NOW() WHERE oid_doc=:id",
            [':s' => $statut, ':id' => $id]);
    }

    public function modifierObservations(int $id, string $obs): void
    {
        $this->db->execute(
            "UPDATE commande_fournisseur SET observations=:o, updated_at=NOW() WHERE oid_doc=:id",
            [':o' => $obs, ':id' => $id]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE commande_fournisseur SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }

    public function aDesReceptions(int $id): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM bon_reception WHERE id_commande_f=:id AND deleted_at IS NULL", [':id' => $id]);
        return (int)($r['n'] ?? 0) > 0;
    }

    public function nbLignes(int $id): int
    {
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM ligne_commande_f WHERE id_commande_f=:id", [':id' => $id]);
        return (int)($r['n'] ?? 0);
    }

    public function statsResumees(): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE statut='en_attente') AS en_attente,
                    COUNT(*) FILTER (WHERE statut='validee')    AS validees,
                    COUNT(*) FILTER (WHERE statut='recue')      AS recues,
                    COALESCE(SUM(montant_total) FILTER (WHERE statut<>'annulee'),0) AS montant_total
             FROM commande_fournisseur WHERE deleted_at IS NULL") ?: [];
    }

    private function buildWhere(array $f): array
    {
        $where  = ['cf.deleted_at IS NULL'];
        $params = [];
        if (!empty($f['recherche'])) { $where[]="(cf.numero ILIKE :r OR f.nom ILIKE :r)"; $params[':r']='%'.$f['recherche'].'%'; }
        if (!empty($f['statut']))    { $where[]="cf.statut=:s::t_statut_commande_f"; $params[':s']=$f['statut']; }
        if (!empty($f['id_fournisseur'])) { $where[]="cf.id_fournisseur=:fid"; $params[':fid']=(int)$f['id_fournisseur']; }
        if (!empty($f['date_debut'])){ $where[]="cf.date_document>=:dd"; $params[':dd']=$f['date_debut']; }
        if (!empty($f['date_fin']))  { $where[]="cf.date_document<=:df"; $params[':df']=$f['date_fin']; }
        return [implode(' AND ', $where), $params];
    }
}
