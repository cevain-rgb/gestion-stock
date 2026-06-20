<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Models;
use App\Core\Database;

class FactureFournisseurModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT ff.oid_doc, ff.numero, ff.date_document, ff.montant_ht, ff.taux_tva,
                    ff.montant_tva, ff.montant_ttc, ff.statut_paiement,
                    cf.numero AS numero_commande, f.nom AS fournisseur,
                    facture_f_reste(ff.oid_doc) AS reste_a_payer
             FROM facture_fournisseur ff
             JOIN commande_fournisseur cf ON cf.oid_doc = ff.id_commande_f
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             WHERE {$where}
             ORDER BY ff.date_document DESC, ff.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM facture_fournisseur ff
             JOIN commande_fournisseur cf ON cf.oid_doc=ff.id_commande_f
             JOIN fournisseur f ON f.id_fournisseur=cf.id_fournisseur WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT ff.*, cf.numero AS numero_commande, f.nom AS fournisseur, f.id_fournisseur,
                    facture_f_reste(ff.oid_doc) AS reste_a_payer
             FROM facture_fournisseur ff
             JOIN commande_fournisseur cf ON cf.oid_doc = ff.id_commande_f
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             WHERE ff.oid_doc=:id AND ff.deleted_at IS NULL", [':id' => $id]);
    }

    public function reglements(int $idFacture): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, b.nom AS banque_nom FROM reglement_fournisseur r
             LEFT JOIN banque b ON b.id_banque = r.id_banque
             WHERE r.id_facture_f=:id ORDER BY r.date_reglement DESC", [':id' => $idFacture]);
    }

    public function creer(int $idCommande, int $idUtilisateur, float $montantHt, float $tauxTva, int $montantTtcOverride = null): int
    {
        $montantTtc = $montantHt * (1 + $tauxTva / 100);
        $this->db->execute(
            "INSERT INTO facture_fournisseur(id_commande_f, id_utilisateur, montant_ht, taux_tva, montant_ttc, date_document)
             VALUES(:c,:u,:ht,:tva,:ttc,CURRENT_DATE)",
            [':c' => $idCommande, ':u' => $idUtilisateur, ':ht' => $montantHt, ':tva' => $tauxTva, ':ttc' => $montantTtc]);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM facture_fournisseur WHERE id_commande_f=:c AND id_utilisateur=:u",
            [':c' => $idCommande, ':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    public function existeePourCommande(int $idCommande): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM facture_fournisseur WHERE id_commande_f=:id AND deleted_at IS NULL", [':id' => $idCommande]);
        return (int)($r['n'] ?? 0) > 0;
    }

    public function mettreAJourStatutPaiement(int $idFacture): void
    {
        $f = $this->trouverParId($idFacture);
        if (!$f) return;
        $reste = (float)$f['reste_a_payer'];
        $statut = $reste <= 0.01 ? 'soldee' : ($reste < (float)$f['montant_ttc'] ? 'partielle' : 'impayee');
        $this->db->execute(
            "UPDATE facture_fournisseur SET statut_paiement=:s::t_statut_paiement, updated_at=NOW() WHERE oid_doc=:id",
            [':s' => $statut, ':id' => $idFacture]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE facture_fournisseur SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }

    public function statsResumees(): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE statut_paiement='impayee')  AS impayees,
                    COUNT(*) FILTER (WHERE statut_paiement='partielle') AS partielles,
                    COUNT(*) FILTER (WHERE statut_paiement='soldee')   AS soldees,
                    COALESCE(SUM(facture_f_reste(oid_doc)) FILTER (WHERE statut_paiement<>'soldee'),0) AS total_du
             FROM facture_fournisseur WHERE deleted_at IS NULL") ?: [];
    }

    private function buildWhere(array $f): array
    {
        $where  = ['ff.deleted_at IS NULL'];
        $params = [];
        if (!empty($f['recherche'])) { $where[]="(ff.numero ILIKE :r OR f.nom ILIKE :r)"; $params[':r']='%'.$f['recherche'].'%'; }
        if (!empty($f['statut']))    { $where[]="ff.statut_paiement=:s::t_statut_paiement"; $params[':s']=$f['statut']; }
        if (!empty($f['date_debut'])){ $where[]="ff.date_document>=:dd"; $params[':dd']=$f['date_debut']; }
        if (!empty($f['date_fin']))  { $where[]="ff.date_document<=:df"; $params[':df']=$f['date_fin']; }
        return [implode(' AND ', $where), $params];
    }
}
