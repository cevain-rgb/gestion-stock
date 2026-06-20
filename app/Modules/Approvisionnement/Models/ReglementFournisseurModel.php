<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Models;
use App\Core\Database;

class ReglementFournisseurModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function creer(int $idFacture, ?int $idBanque, float $montant, string $date, string $mode, string $ref): int
    {
        $this->db->execute(
            "INSERT INTO reglement_fournisseur(id_facture_f, id_banque, montant, date_reglement, mode_paiement, reference)
             VALUES(:f,:b,:m,:d,:mode::t_mode_paiement,:ref)",
            [':f' => $idFacture, ':b' => $idBanque, ':m' => $montant, ':d' => $date, ':mode' => $mode, ':ref' => $ref]);
        $r = $this->db->fetchOne(
            "SELECT MAX(id_reglement_f) AS id FROM reglement_fournisseur WHERE id_facture_f=:f", [':f' => $idFacture]);
        return (int)$r['id'];
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("DELETE FROM reglement_fournisseur WHERE id_reglement_f=:id", [':id' => $id]);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne("SELECT * FROM reglement_fournisseur WHERE id_reglement_f=:id", [':id' => $id]);
    }
}
