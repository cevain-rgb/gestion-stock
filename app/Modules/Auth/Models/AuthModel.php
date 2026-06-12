<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Core\Database;

class AuthModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Vérifie les identifiants via la fonction SQL sécurisée.
     */
    public function verifierIdentifiants(string $login, string $motDePasse): bool
    {
        $row = $this->db->fetchOne(
            'SELECT utilisateur_verifier_mdp(:login, :mdp) AS ok',
            [':login' => $login, ':mdp' => $motDePasse]
        );
        return $row && $row['ok'] === true;
    }

    /**
     * Retourne l'utilisateur actif par son login.
     */
    public function trouverParLogin(string $login): array|false
    {
        return $this->db->fetchOne(
            'SELECT u.id_utilisateur, u.nom, u.prenom, u.login, u.actif,
                    u.id_groupe, g.libelle AS groupe_libelle
             FROM utilisateur u
             JOIN groupe_utilisateur g ON g.id_groupe = u.id_groupe
             WHERE u.login = :login
               AND u.actif = TRUE
               AND u.deleted_at IS NULL',
            [':login' => $login]
        );
    }

    /**
     * Retourne les droits d'un groupe sous forme de tableau indexé.
     * Résultat : ['approvisionnement.creer' => true, ...]
     */
    public function droitsGroupe(int $groupeId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT module, action, autorise FROM droit WHERE id_groupe = :g',
            [':g' => $groupeId]
        );
        $droits = [];
        foreach ($rows as $row) {
            $droits[$row['module'] . '.' . $row['action']] = (bool)$row['autorise'];
        }
        return $droits;
    }

    /**
     * Journalise la connexion/déconnexion dans journal_audit.
     */
    public function journaliser(int $userId, string $action, string $ip): void
    {
        $this->db->execute(
            "INSERT INTO journal_audit (id_utilisateur, table_cible, action, ip_adresse)
             VALUES (:u, 'utilisateur', :a::t_action_audit, :ip::inet)",
            [':u' => $userId, ':a' => $action, ':ip' => $ip]
        );
    }
}
