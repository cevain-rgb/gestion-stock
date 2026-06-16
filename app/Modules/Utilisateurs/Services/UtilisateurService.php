<?php
declare(strict_types=1);
namespace App\Modules\Utilisateurs\Services;

use App\Modules\Utilisateurs\Models\UtilisateurModel;
use App\Modules\Utilisateurs\Models\GroupeModel;
use App\Modules\Utilisateurs\Models\DroitModel;
use App\Core\Database;

class UtilisateurService
{
    private UtilisateurModel $userModel;
    private GroupeModel      $groupeModel;
    private DroitModel       $droitModel;

    public function __construct()
    {
        $this->userModel   = new UtilisateurModel();
        $this->groupeModel = new GroupeModel();
        $this->droitModel  = new DroitModel();
    }

    // ── Groupes ────────────────────────────────────────────────────────────

    public function listerGroupes(int $page = 1): array
    {
        return [
            'lignes'  => $this->groupeModel->tous($page),
            'total'   => $this->groupeModel->compter(),
            'page'    => $page,
            'perPage' => 20,
        ];
    }

    public function creerGroupe(array $data): array
    {
        $errors = $this->validerGroupe($data);
        if ($errors) return ['ok' => false, 'errors' => $errors];

        if ($this->groupeModel->libelleExiste($data['libelle'])) {
            return ['ok' => false, 'errors' => ['libelle' => 'Ce libellé existe déjà.']];
        }

        $id = $this->groupeModel->creer(
            trim($data['libelle']),
            trim($data['description'] ?? '')
        );
        $this->journaliser('INSERT', 'groupe_utilisateur', $id, null, $data);
        return ['ok' => true, 'id' => $id];
    }

    public function modifierGroupe(int $id, array $data): array
    {
        $groupe = $this->groupeModel->trouverParId($id);
        if (!$groupe) return ['ok' => false, 'errors' => ['global' => 'Groupe introuvable.']];

        $errors = $this->validerGroupe($data);
        if ($errors) return ['ok' => false, 'errors' => $errors];

        if ($this->groupeModel->libelleExiste($data['libelle'], $id)) {
            return ['ok' => false, 'errors' => ['libelle' => 'Ce libellé existe déjà.']];
        }

        $this->groupeModel->modifier($id, trim($data['libelle']), trim($data['description'] ?? ''));
        $this->journaliser('UPDATE', 'groupe_utilisateur', $id, $groupe, $data);
        return ['ok' => true];
    }

    public function supprimerGroupe(int $id): array
    {
        if ($this->groupeModel->aDesUtilisateurs($id)) {
            return ['ok' => false, 'message' => 'Ce groupe contient des utilisateurs actifs. Réaffectez-les avant de supprimer.'];
        }
        $ancien = $this->groupeModel->trouverParId($id);
        $this->groupeModel->supprimer($id);
        $this->archivageXml('groupe_utilisateur', $id, $ancien);
        $this->journaliser('DELETE', 'groupe_utilisateur', $id, $ancien, null);
        return ['ok' => true];
    }

    // ── Droits ─────────────────────────────────────────────────────────────

    public function matriceDroits(int $groupeId): array
    {
        return $this->droitModel->matriceGroupe($groupeId);
    }

    public function sauvegarderDroits(int $groupeId, array $postData): void
    {
        // Construire le tableau des cases cochées [module.action => true]
        $coches = [];
        foreach (DroitModel::MODULES as $m) {
            foreach (DroitModel::ACTIONS as $a) {
                $key = $m . '_' . $a;
                if (!empty($postData[$key])) $coches[$key] = true;
            }
        }
        $this->droitModel->sauvegarderMatrice($groupeId, $coches);
        $this->journaliser('UPDATE', 'droit', $groupeId, null, $coches);
    }

    // ── Utilisateurs ───────────────────────────────────────────────────────

    public function listerUtilisateurs(int $page = 1, array $filtres = []): array
    {
        return [
            'lignes'  => $this->userModel->tous($page, 20, $filtres),
            'total'   => $this->userModel->compter($filtres),
            'page'    => $page,
            'perPage' => 20,
            'filtres' => $filtres,
        ];
    }

    public function creerUtilisateur(array $data): array
    {
        $errors = $this->validerUtilisateur($data, true);
        if ($errors) return ['ok' => false, 'errors' => $errors];

        if ($this->userModel->loginExiste($data['login'])) {
            return ['ok' => false, 'errors' => ['login' => 'Ce login est déjà utilisé.']];
        }

        $id = $this->userModel->creer(
            (int)$data['id_groupe'],
            trim($data['nom']),
            trim($data['prenom'] ?? ''),
            trim($data['login']),
            $data['password']
        );
        $this->journaliser('INSERT', 'utilisateur', $id, null, array_diff_key($data, ['password' => '', 'password_confirm' => '']));
        return ['ok' => true, 'id' => $id];
    }

    public function modifierUtilisateur(int $id, array $data): array
    {
        $user = $this->userModel->trouverParId($id);
        if (!$user) return ['ok' => false, 'errors' => ['global' => 'Utilisateur introuvable.']];

        $errors = $this->validerUtilisateur($data, false);
        if ($errors) return ['ok' => false, 'errors' => $errors];

        if ($this->userModel->loginExiste($data['login'], $id)) {
            return ['ok' => false, 'errors' => ['login' => 'Ce login est déjà utilisé.']];
        }

        // Empêcher un admin de se rétrograder lui-même
        if ((int)$_SESSION['user_id'] === $id) {
            $groupeActuel = $this->groupeModel->trouverParId((int)$_SESSION['groupe_id']);
            if ($groupeActuel && strtolower($groupeActuel['libelle']) === 'administrateur') {
                $nouveauGroupe = $this->groupeModel->trouverParId((int)$data['id_groupe']);
                if (!$nouveauGroupe || strtolower($nouveauGroupe['libelle']) !== 'administrateur') {
                    return ['ok' => false, 'errors' => ['id_groupe' => 'Vous ne pouvez pas changer votre propre groupe Administrateur.']];
                }
            }
        }

        $this->userModel->modifier($id, (int)$data['id_groupe'], trim($data['nom']),
            trim($data['prenom'] ?? ''), trim($data['login']), !empty($data['actif']));
        $this->journaliser('UPDATE', 'utilisateur', $id, $user, array_diff_key($data, ['password' => '']));
        return ['ok' => true];
    }

    public function changerMotDePasse(int $id, array $data): array
    {
        if (empty($data['password']) || strlen($data['password']) < 6) {
            return ['ok' => false, 'errors' => ['password' => 'Le mot de passe doit faire au moins 6 caractères.']];
        }
        if ($data['password'] !== ($data['password_confirm'] ?? '')) {
            return ['ok' => false, 'errors' => ['password_confirm' => 'Les mots de passe ne correspondent pas.']];
        }
        $this->userModel->changerMotDePasse($id, $data['password']);
        $this->journaliser('UPDATE', 'utilisateur', $id, null, ['action' => 'changement_mdp']);
        return ['ok' => true];
    }

    public function basculerActif(int $id): array
    {
        if ((int)$_SESSION['user_id'] === $id) {
            return ['ok' => false, 'message' => 'Vous ne pouvez pas désactiver votre propre compte.'];
        }
        $this->userModel->basculerActif($id);
        return ['ok' => true];
    }

    public function supprimerUtilisateur(int $id): array
    {
        if ((int)$_SESSION['user_id'] === $id) {
            return ['ok' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'];
        }
        $user = $this->userModel->trouverParId($id);
        if (!$user) return ['ok' => false, 'message' => 'Utilisateur introuvable.'];

        $this->archivageXml('utilisateur', $id, $user);
        $this->userModel->supprimer($id);
        $this->journaliser('DELETE', 'utilisateur', $id, $user, null);
        return ['ok' => true];
    }

    // ── Profil ─────────────────────────────────────────────────────────────

    public function profilConnecte(): array|false
    {
        return $this->userModel->trouverParId((int)$_SESSION['user_id']);
    }

    public function historiqueConnexions(): array
    {
        return $this->userModel->historiqueConnexions((int)$_SESSION['user_id']);
    }

    // ── Utilitaires internes ───────────────────────────────────────────────

    private function validerGroupe(array $data): array
    {
        $errors = [];
        if (empty(trim($data['libelle'] ?? '')))
            $errors['libelle'] = 'Le libellé est obligatoire.';
        elseif (strlen($data['libelle']) > 100)
            $errors['libelle'] = 'Le libellé ne peut dépasser 100 caractères.';
        return $errors;
    }

    private function validerUtilisateur(array $data, bool $creation): array
    {
        $errors = [];
        if (empty(trim($data['nom'] ?? '')))
            $errors['nom'] = 'Le nom est obligatoire.';
        if (empty(trim($data['login'] ?? '')))
            $errors['login'] = 'Le login est obligatoire.';
        elseif (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $data['login']))
            $errors['login'] = 'Login invalide (3-50 caractères alphanumériques, ., -, _).';
        if (empty($data['id_groupe']))
            $errors['id_groupe'] = 'Le groupe est obligatoire.';
        if ($creation) {
            if (empty($data['password']))
                $errors['password'] = 'Le mot de passe est obligatoire.';
            elseif (strlen($data['password']) < 6)
                $errors['password'] = 'Minimum 6 caractères.';
            elseif ($data['password'] !== ($data['password_confirm'] ?? ''))
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
        }
        return $errors;
    }

    private function journaliser(string $action, string $table, int $id, mixed $ancien, mixed $nouveau): void
    {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO journal_audit(id_utilisateur, table_cible, action, id_enregistrement, anciennes_valeurs, nouvelles_valeurs, ip_adresse)
             VALUES(:u, :t, :a::t_action_audit, :id, :old::jsonb, :new::jsonb, :ip::inet)",
            [
                ':u'   => $_SESSION['user_id'] ?? null,
                ':t'   => $table,
                ':a'   => $action,
                ':id'  => $id,
                ':old' => $ancien ? json_encode($ancien) : null,
                ':new' => $nouveau ? json_encode($nouveau) : null,
                ':ip'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]
        );
    }

    private function archivageXml(string $entite, int $id, array $data): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<archive entite="' . htmlspecialchars($entite) . '" id="' . $id . '"'
              . ' action="suppression"'
              . ' date="' . date('c') . '"'
              . ' utilisateur="' . htmlspecialchars($_SESSION['user_login'] ?? '') . '">' . "\n";
        foreach ($data as $col => $val) {
            $xml .= '  <champ nom="' . htmlspecialchars((string)$col) . '">'
                  . htmlspecialchars((string)$val) . "</champ>\n";
        }
        $xml .= '</archive>';

        // BDD
        Database::getInstance()->execute(
            "INSERT INTO archive_xml(entite, id_entite, xml_data, action, id_utilisateur)
             VALUES(:e, :id, :xml, 'suppression', :u)",
            [':e' => $entite, ':id' => $id, ':xml' => $xml, ':u' => $_SESSION['user_id'] ?? null]
        );

        // Fichier disque
        $dir = BASE_PATH . '/storage/archives/' . $entite;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . '/' . $id . '_' . time() . '.xml', $xml);
    }
}
