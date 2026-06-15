<?php
declare(strict_types=1);
namespace App\Shared\Traits;
use App\Core\Database;

/**
 * Trait à inclure dans tous les Services métier.
 * Fournit journaliser() et archivageXml() de manière centralisée.
 */
trait AuditableTrait
{
    protected function journaliser(
        string $action,
        string $table,
        int    $idEnregistrement,
        mixed  $anciennesValeurs = null,
        mixed  $nouvellesValeurs = null
    ): void {
        try {
            Database::getInstance()->execute(
                "INSERT INTO journal_audit
                    (id_utilisateur, table_cible, action, id_enregistrement,
                     anciennes_valeurs, nouvelles_valeurs, ip_adresse)
                 VALUES (:u, :t, :a::t_action_audit, :id, :old::jsonb, :new::jsonb, :ip::inet)",
                [
                    ':u'   => $_SESSION['user_id'] ?? null,
                    ':t'   => $table,
                    ':a'   => $action,
                    ':id'  => $idEnregistrement,
                    ':old' => $anciennesValeurs ? json_encode($anciennesValeurs) : null,
                    ':new' => $nouvellesValeurs ? json_encode($nouvellesValeurs) : null,
                    ':ip'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                ]
            );
        } catch (\Throwable $e) {
            error_log('[Audit] Journalisation échouée : ' . $e->getMessage());
        }
    }

    protected function archivageXml(string $entite, int $id, array $data): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<archive entite="' . htmlspecialchars($entite, ENT_XML1) . '"'
              . ' id="' . $id . '"'
              . ' action="suppression"'
              . ' date="' . date('c') . '"'
              . ' utilisateur="' . htmlspecialchars($_SESSION['user_login'] ?? 'system', ENT_XML1) . '">'
              . "\n";
        foreach ($data as $col => $val) {
            $xml .= '  <champ nom="' . htmlspecialchars((string)$col, ENT_XML1) . '">'
                  . htmlspecialchars((string)$val, ENT_XML1)
                  . "</champ>\n";
        }
        $xml .= '</archive>';

        // Persistance BDD
        try {
            Database::getInstance()->execute(
                "INSERT INTO archive_xml(entite, id_entite, xml_data, action, id_utilisateur)
                 VALUES (:e, :id, :xml, 'suppression', :u)",
                [
                    ':e'   => $entite,
                    ':id'  => $id,
                    ':xml' => $xml,
                    ':u'   => $_SESSION['user_id'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            error_log('[Archive] BDD échouée : ' . $e->getMessage());
        }

        // Persistance disque
        try {
            $dir = BASE_PATH . '/storage/archives/' . $entite;
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            file_put_contents($dir . '/' . $id . '_' . time() . '.xml', $xml);
        } catch (\Throwable $e) {
            error_log('[Archive] Disque échoué : ' . $e->getMessage());
        }
    }
}
