<?php
declare(strict_types=1);
namespace App\Modules\Structure\Models;
use App\Core\Database;

class BanqueModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function toutes(): array
    {
        return $this->db->fetchAll(
            "SELECT b.id_banque, b.nom, b.numero_compte, b.created_at,
                    (b.adresse).ville AS ville,
                    COALESCE(SUM(v.montant),0) AS total_verses
             FROM banque b
             LEFT JOIN versement_banque v ON v.id_banque = b.id_banque
             GROUP BY b.id_banque, b.nom, b.numero_compte, b.created_at, b.adresse
             ORDER BY b.nom");
    }

    public function toutesOptions(): array
    {
        return $this->db->fetchAll("SELECT id_banque, nom, numero_compte FROM banque ORDER BY nom");
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT b.*, (b.adresse).rue AS rue, (b.adresse).ville AS ville,
                    (b.adresse).code_postal AS code_postal, (b.adresse).pays AS pays
             FROM banque b WHERE id_banque=:id", [':id' => $id]);
    }

    public function versements(int $id): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM versement_banque WHERE id_banque=:id ORDER BY date_versement DESC LIMIT 20",
            [':id' => $id]);
    }

    public function creer(array $d): int
    {
        $this->db->execute(
            "INSERT INTO banque(nom, numero_compte, adresse)
             VALUES(:n,:nc,ROW(:rue,:ville,:cp,:pays)::t_adresse)",
            $this->bind($d));
        return (int)$this->db->lastInsertId('banque_id_banque_seq');
    }

    public function modifier(int $id, array $d): void
    {
        $p = $this->bind($d); $p[':id'] = $id;
        $this->db->execute(
            "UPDATE banque SET nom=:n, numero_compte=:nc,
             adresse=ROW(:rue,:ville,:cp,:pays)::t_adresse WHERE id_banque=:id", $p);
    }

    public function supprimer(int $id): void
    {
        $this->db->execute("DELETE FROM banque WHERE id_banque=:id", [':id' => $id]);
    }

    public function ajouterVersement(int $idBanque, float $montant, string $date, string $ref): void
    {
        $this->db->execute(
            "INSERT INTO versement_banque(id_banque, montant, date_versement, reference)
             VALUES(:b,:m,:d,:r)",
            [':b' => $idBanque, ':m' => $montant, ':d' => $date, ':r' => $ref]);
    }

    private function bind(array $d): array
    {
        return [
            ':n'    => trim($d['nom']),
            ':nc'   => trim($d['numero_compte'] ?? ''),
            ':rue'  => trim($d['rue'] ?? ''),
            ':ville'=> trim($d['ville'] ?? ''),
            ':cp'   => trim($d['code_postal'] ?? ''),
            ':pays' => trim($d['pays'] ?? ''),
        ];
    }
}
