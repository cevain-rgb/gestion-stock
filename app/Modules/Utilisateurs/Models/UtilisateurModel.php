<?php
declare(strict_types=1);
namespace App\Modules\Utilisateurs\Models;
use App\Core\Database;

class UtilisateurModel
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function tous(int $page = 1, int $perPage = 20, array $filtres = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ['u.deleted_at IS NULL'];
        $params = [];

        if (!empty($filtres['recherche'])) {
            $where[] = "(u.nom ILIKE :rech OR u.prenom ILIKE :rech OR u.login ILIKE :rech)";
            $params[':rech'] = '%' . $filtres['recherche'] . '%';
        }
        if (isset($filtres['actif']) && $filtres['actif'] !== '') {
            $where[] = "u.actif = :actif";
            $params[':actif'] = $filtres['actif'] === '1' ? 'TRUE' : 'FALSE';
        }
        if (!empty($filtres['id_groupe'])) {
            $where[] = "u.id_groupe = :groupe";
            $params[':groupe'] = (int)$filtres['id_groupe'];
        }

        $sql = "SELECT u.id_utilisateur, u.nom, u.prenom, u.login, u.actif,
                       u.created_at, g.libelle AS groupe_libelle, g.id_groupe
                FROM utilisateur u
                JOIN groupe_utilisateur g ON g.id_groupe = u.id_groupe
                WHERE " . implode(' AND ', $where) . "
                ORDER BY u.nom, u.prenom
                LIMIT :limit OFFSET :offset";
        $params[':limit']  = $perPage;
        $params[':offset'] = $offset;
        return $this->db->fetchAll($sql, $params);
    }

    public function compter(array $filtres = []): int
    {
        $where  = ['u.deleted_at IS NULL'];
        $params = [];
        if (!empty($filtres['recherche'])) {
            $where[] = "(u.nom ILIKE :rech OR u.prenom ILIKE :rech OR u.login ILIKE :rech)";
            $params[':rech'] = '%' . $filtres['recherche'] . '%';
        }
        if (isset($filtres['actif']) && $filtres['actif'] !== '') {
            $where[] = "u.actif = :actif";
            $params[':actif'] = $filtres['actif'] === '1' ? 'TRUE' : 'FALSE';
        }
        if (!empty($filtres['id_groupe'])) {
            $where[] = "u.id_groupe = :groupe";
            $params[':groupe'] = (int)$filtres['id_groupe'];
        }
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM utilisateur u WHERE " . implode(' AND ', $where),
            $params
        );
        return (int)($r['n'] ?? 0);
    }

    public function trouverParId(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT u.id_utilisateur, u.nom, u.prenom, u.login, u.actif, u.id_groupe,
                    g.libelle AS groupe_libelle
             FROM utilisateur u
             JOIN groupe_utilisateur g ON g.id_groupe = u.id_groupe
             WHERE u.id_utilisateur = :id AND u.deleted_at IS NULL",
            [':id' => $id]
        );
    }

    public function creer(int $groupe, string $nom, string $prenom, string $login, string $password): int
    {
        // Le trigger SQL hashera le mot de passe
        $this->db->execute(
            "INSERT INTO utilisateur(id_groupe, nom, prenom, login, password_hash, actif)
             VALUES(:g, :n, :p, :l, :pwd, TRUE)",
            [':g' => $groupe, ':n' => $nom, ':p' => $prenom, ':l' => $login, ':pwd' => $password]
        );
        return (int)$this->db->lastInsertId('entite_oid_entite_seq');
    }

    public function modifier(int $id, int $groupe, string $nom, string $prenom, string $login, bool $actif): void
    {
        $this->db->execute(
            "UPDATE utilisateur SET id_groupe=:g, nom=:n, prenom=:p, login=:l, actif=:a, updated_at=NOW()
             WHERE id_utilisateur=:id",
            [':g' => $groupe, ':n' => $nom, ':p' => $prenom, ':l' => $login,
             ':a' => $actif ? 'TRUE' : 'FALSE', ':id' => $id]
        );
    }

    public function changerMotDePasse(int $id, string $nouveauMdp): void
    {
        // La mise à jour de password_hash déclenche le trigger de hashage
        $this->db->execute(
            "UPDATE utilisateur SET password_hash=:pwd, updated_at=NOW() WHERE id_utilisateur=:id",
            [':pwd' => $nouveauMdp, ':id' => $id]
        );
    }

    public function basculerActif(int $id): void
    {
        $this->db->execute(
            "UPDATE utilisateur SET actif = NOT actif, updated_at=NOW() WHERE id_utilisateur=:id",
            [':id' => $id]
        );
    }

    public function supprimer(int $id): void
    {
        $this->db->execute(
            "UPDATE utilisateur SET deleted_at=NOW() WHERE id_utilisateur=:id",
            [':id' => $id]
        );
    }

    public function loginExiste(string $login, int $excludeId = 0): bool
    {
        $r = $this->db->fetchOne(
            "SELECT COUNT(*) AS n FROM utilisateur WHERE login=:l AND deleted_at IS NULL AND id_utilisateur <> :ex",
            [':l' => $login, ':ex' => $excludeId]
        );
        return ((int)($r['n'] ?? 0)) > 0;
    }

    public function historiqueConnexions(int $id, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT action, ip_adresse, created_at
             FROM journal_audit
             WHERE id_utilisateur=:id AND action IN ('CONNEXION','DECONNEXION')
             ORDER BY created_at DESC LIMIT :lim",
            [':id' => $id, ':lim' => $limit]
        );
    }
}
