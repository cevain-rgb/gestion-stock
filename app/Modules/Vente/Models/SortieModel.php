<?php
declare(strict_types=1);
namespace App\Modules\Vente\Models;
use App\Core\Database;

class SortieModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page, int $perPage, array $filtres): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ['bs.deleted_at IS NULL'];
        $params = [];
        if (!empty($filtres['recherche'])) { $where[]="bs.numero ILIKE :r"; $params[':r']='%'.$filtres['recherche'].'%'; }
        if (!empty($filtres['motif']))     { $where[]="bs.motif=:m::t_motif_sortie"; $params[':m']=$filtres['motif']; }
        return $this->db->fetchAll(
            "SELECT bs.oid_doc, bs.numero, bs.date_document, bs.motif, bs.observations,
                    COALESCE(SUM(ls.quantite * ls.valeur_unitaire),0) AS valeur_totale
             FROM bon_sortie bs
             LEFT JOIN ligne_sortie ls ON ls.id_sortie = bs.oid_doc
             WHERE " . implode(' AND ', $where) . "
             GROUP BY bs.oid_doc, bs.numero, bs.date_document, bs.motif, bs.observations
             ORDER BY bs.date_document DESC, bs.oid_doc DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, [':limit' => $perPage, ':offset' => $offset]));
    }

    public function compter(array $filtres): int
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];
        if (!empty($filtres['recherche'])) { $where[]="numero ILIKE :r"; $params[':r']='%'.$filtres['recherche'].'%'; }
        if (!empty($filtres['motif']))     { $where[]="motif=:m::t_motif_sortie"; $params[':m']=$filtres['motif']; }
        $r = $this->db->fetchOne("SELECT COUNT(*) AS n FROM bon_sortie WHERE " . implode(' AND ', $where), $params);
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT bs.*, u.login AS cree_par
             FROM bon_sortie bs JOIN utilisateur u ON u.id_utilisateur = bs.id_utilisateur
             WHERE bs.oid_doc=:id AND bs.deleted_at IS NULL", [':id' => $id]);
    }

    public function lignes(int $idSortie): array
    {
        return $this->db->fetchAll(
            "SELECT ls.id_ligne_s, ls.id_produit, ls.quantite, ls.valeur_unitaire, ls.motif_detail,
                    (ls.quantite * ls.valeur_unitaire) AS montant_ligne,
                    p.code, p.designation, p.unite
             FROM ligne_sortie ls
             JOIN produit p ON p.id_produit = ls.id_produit
             WHERE ls.id_sortie=:id ORDER BY ls.id_ligne_s", [':id' => $idSortie]);
    }

    public function creer(string $motif, int $idUtilisateur, string $observations): int
    {
        $this->db->execute(
            "INSERT INTO bon_sortie(motif, id_utilisateur, observations, date_document)
             VALUES(:m::t_motif_sortie,:u,:obs,CURRENT_DATE)",
            [':m' => $motif, ':u' => $idUtilisateur, ':obs' => $observations]);
        $r = $this->db->fetchOne("SELECT MAX(oid_doc) AS id FROM bon_sortie WHERE id_utilisateur=:u", [':u' => $idUtilisateur]);
        return (int)$r['id'];
    }

    /** L'insertion déclenche trg_sortie_stock (AFTER INSERT) qui décrémente le stock */
    public function ajouterLigne(int $idSortie, int $idProduit, float $qte, float $valeurUnit, string $detail): void
    {
        $this->db->execute(
            "INSERT INTO ligne_sortie(id_sortie, id_produit, quantite, valeur_unitaire, motif_detail)
             VALUES(:s,:p,:q,:vu,:d)",
            [':s' => $idSortie, ':p' => $idProduit, ':q' => $qte, ':vu' => $valeurUnit, ':d' => $detail]);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("UPDATE bon_sortie SET deleted_at=NOW() WHERE oid_doc=:id", [':id' => $id]);
    }
}
