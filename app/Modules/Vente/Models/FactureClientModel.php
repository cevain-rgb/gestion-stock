<?php
declare(strict_types=1);
namespace App\Modules\Vente\Models;
use App\Core\Database;

class FactureClientModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        [$where, $params] = $this->buildWhere($filtres);
        return $this->db->fetchAll(
            "SELECT fc.oid_doc, fc.numero, fc.date_document, fc.montant_ht, fc.taux_tva,
                    fc.montant_tva, fc.montant_ttc, fc.statut_paiement,
                    cc.numero AS numero_commande, c.nom AS client,
                    facture_c_reste(fc.oid_doc) AS reste_a_payer
             FROM facture_client fc
             JOIN commande_client cc ON cc.oid_doc = fc.id_commande_c
             JOIN client c ON c.id_client = cc.id_client
             WHERE {$where}
             ORDER BY fc.date_document DESC, fc.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        [$where, $params] = $this->buildWhere($filtres);
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM facture_client fc
             JOIN commande_client cc ON cc.oid_doc=fc.id_commande_c
             JOIN client c ON c.id_client=cc.id_client WHERE {$where}", $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT fc.*, cc.numero AS numero_commande, c.nom AS client, c.id_client,
                    facture_c_reste(fc.oid_doc) AS reste_a_payer
             FROM facture_client fc
             JOIN commande_client cc ON cc.oid_doc = fc.id_commande_c
             JOIN client c ON c.id_client = cc.id_client
             WHERE fc.oid_doc=:id AND fc.deleted_at IS NULL", [':id' => $id]);
    }

    public function reglements(int $idFacture): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, b.nom AS banque_nom FROM reglement_client r
             LEFT JOIN banque b ON b.id_banque = r.id_banque
             WHERE r.id_facture_c=:id ORDER BY r.date_reglement DESC", [':id' => $idFacture]);
    }

    public function creer(int $idCommande, int $idUtilisateur, float $montantHt, float $tauxTva): int
    {
        $montantTtc = $montantHt * (1 + $tauxTva / 100);
        $this->db->execute(
            "INSERT INTO facture_client(id_commande_c, id_utilisateur, montant_ht, taux_tva, montant_ttc, date_document)
             VALUES(:c,:u,:ht,:tva,:ttc,CURRENT_DATE)",
            [':c' => $idCommande, ':u' => $idUtilisateur, ':ht' => $montantHt, ':tva' => $tauxTva, ':ttc' => $montantTtc]);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM facture_client WHERE id_commande_c=:c AND id_utilisateur=:u",
            [':c' => $idCommande, ':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    public function existeePourCommande(int $idCommande): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM facture_client WHERE id_commande_c=:id AND deleted_at IS NULL", [':id' => $idCommande]);
        return (int)($r['n'] ?? 0) > 0;
    }

    public function mettreAJourStatutPaiement(int $idFacture): void
    {
        $f = $this->trouverParId($idFacture);
        if (!$f) return;
        $reste = (float)$f['reste_a_payer'];
        $statut = $reste <= 0.01 ? 'soldee' : ($reste < (float)$f['montant_ttc'] ? 'partielle' : 'impayee');
        $this->db->execute(
            "UPDATE facture_client SET statut_paiement=:s::t_statut_paiement, updated_at=NOW() WHERE oid_doc=:id",
            [':s' => $statut, ':id' => $idFacture]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE facture_client SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }

    public function statsResumees(): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE statut_paiement='impayee')  AS impayees,
                    COUNT(*) FILTER (WHERE statut_paiement='partielle') AS partielles,
                    COUNT(*) FILTER (WHERE statut_paiement='soldee')   AS soldees,
                    COALESCE(SUM(facture_c_reste(oid_doc)) FILTER (WHERE statut_paiement<>'soldee'),0) AS total_du
             FROM facture_client WHERE deleted_at IS NULL") ?: [];
    }

    private function buildWhere(array $f): array
    {
        $where  = ['fc.deleted_at IS NULL'];
        $params = [];
        if (!empty($f['recherche'])) { $where[]="(fc.numero ILIKE :r OR c.nom ILIKE :r)"; $params[':r']='%'.$f['recherche'].'%'; }
        if (!empty($f['statut']))    { $where[]="fc.statut_paiement=:s::t_statut_paiement"; $params[':s']=$f['statut']; }
        if (!empty($f['date_debut'])){ $where[]="fc.date_document>=:dd"; $params[':dd']=$f['date_debut']; }
        if (!empty($f['date_fin']))  { $where[]="fc.date_document<=:df"; $params[':df']=$f['date_fin']; }
        return [implode(' AND ', $where), $params];
    }
}
