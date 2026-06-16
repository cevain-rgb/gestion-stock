<?php
declare(strict_types=1);
namespace App\Modules\Utilisateurs\Models;
use App\Core\Database;

class DroitModel
{
    private Database $db;

    // Toutes les combinaisons module×action gérées
    public const MODULES  = ['approvisionnement','vente','structure','securite'];
    public const ACTIONS  = ['consulter','creer','modifier','supprimer','imprimer','regler'];

    public function __construct() { $this->db = Database::getInstance(); }

    /** Retourne la matrice des droits d'un groupe [module.action => bool] */
    public function matriceGroupe(int $groupeId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT module, action, autorise FROM droit WHERE id_groupe=:g",
            [':g' => $groupeId]
        );
        $matrice = [];
        // Initialiser tout à false
        foreach (self::MODULES as $m) {
            foreach (self::ACTIONS as $a) {
                $matrice[$m][$a] = false;
            }
        }
        foreach ($rows as $row) {
            $matrice[$row['module']][$row['action']] = (bool)$row['autorise'];
        }
        return $matrice;
    }

    /** Sauvegarde complète de la matrice pour un groupe */
    public function sauvegarderMatrice(int $groupeId, array $coches): void
    {
        $this->db->beginTransaction();
        try {
            // Supprimer les anciens droits
            $this->db->execute("DELETE FROM droit WHERE id_groupe=:g", [':g' => $groupeId]);

            // Réinsérer
            foreach (self::MODULES as $module) {
                foreach (self::ACTIONS as $action) {
                    $key      = $module . '_' . $action;
                    $autorise = isset($coches[$key]) ? 'TRUE' : 'FALSE';
                    $this->db->execute(
                        "INSERT INTO droit(id_groupe, module, action, autorise)
                        VALUES(:g, :m::t_module, :a::t_action_droit, {$autorise})",
                        [':g' => $groupeId, ':m' => $module, ':a' => $action]
                    );
                }
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /** Copie les droits d'un groupe source vers un groupe cible */
    public function copierDroits(int $sourceId, int $cibleId): void
    {
        $this->db->execute("DELETE FROM droit WHERE id_groupe=:g", [':g' => $cibleId]);
        $this->db->execute(
            "INSERT INTO droit(id_groupe, module, action, autorise)
             SELECT :cible, module, action, autorise FROM droit WHERE id_groupe=:source",
            [':cible' => $cibleId, ':source' => $sourceId]
        );
    }
}
