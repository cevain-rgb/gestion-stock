<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Modules\Approvisionnement\Models\{CommandeFournisseurModel, ReceptionModel,
    FactureFournisseurModel, ReglementFournisseurModel, DonModel};

class EditionController extends Controller
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // ── Bon de commande fournisseur ──────────────────────────────────────────
    public function bonCommande(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $m   = new CommandeFournisseurModel();
        $cmd = $m->trouverParId((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande introuvable.'); $this->redirect('/approvisionnement/commandes'); }
        $cmd['lignes'] = $m->lignes((int)$p['id']);
        $this->journaliserImpression('commande_fournisseur', (int)$p['id']);
        $this->render('Approvisionnement/editions/bon_commande', compact('cmd'));
    }

    // ── Bon de réception ─────────────────────────────────────────────────────
    public function bonReception(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $m         = new ReceptionModel();
        $reception = $m->trouverParId((int)$p['id']);
        if (!$reception) { $this->flash('error','Réception introuvable.'); $this->redirect('/approvisionnement/receptions'); }
        $reception['lignes'] = $m->lignes((int)$p['id']);
        $this->journaliserImpression('bon_reception', (int)$p['id']);
        $this->render('Approvisionnement/editions/bon_reception', compact('reception'));
    }

    // ── Facture fournisseur ──────────────────────────────────────────────────
    public function facture(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $m       = new FactureFournisseurModel();
        $facture = $m->trouverParId((int)$p['id']);
        if (!$facture) { $this->flash('error','Facture introuvable.'); $this->redirect('/approvisionnement/factures'); }
        $facture['reglements'] = $m->reglements((int)$p['id']);
        $this->journaliserImpression('facture_fournisseur', (int)$p['id']);
        $this->render('Approvisionnement/editions/facture', compact('facture'));
    }

    // ── Reçu de règlement ────────────────────────────────────────────────────
    public function recu(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $mR      = new ReglementFournisseurModel();
        $mF      = new FactureFournisseurModel();
        $regl    = $mR->trouverParId((int)$p['id']);
        if (!$regl) { $this->flash('error','Règlement introuvable.'); $this->redirect('/approvisionnement/factures'); }
        $facture = $mF->trouverParId((int)$regl['id_facture_f']);
        // Récupérer infos banque si présente
        $banque = null;
        if (!empty($regl['id_banque'])) {
            $banque = $this->db->fetchOne("SELECT nom, numero_compte FROM banque WHERE id_banque=:id", [':id'=>$regl['id_banque']]);
        }
        $this->journaliserImpression('reglement_fournisseur', (int)$p['id']);
        $this->render('Approvisionnement/editions/recu', compact('regl','facture','banque'));
    }

    // ── Bon d'entrée (don) ───────────────────────────────────────────────────
    public function bonEntree(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $m   = new DonModel();
        $don = $m->trouverParId((int)$p['id']);
        if (!$don) { $this->flash('error','Don introuvable.'); $this->redirect('/approvisionnement/dons'); }
        $don['lignes'] = $m->lignes((int)$p['id']);
        $this->journaliserImpression('don', (int)$p['id']);
        $this->render('Approvisionnement/editions/bon_entree', compact('don'));
    }

    // ── État des achats par jour ─────────────────────────────────────────────
    public function etatAchatsJour(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $date   = $_GET['date'] ?? date('Y-m-d');
        $lignes = $this->db->fetchAll(
            "SELECT cf.numero, f.nom AS fournisseur, cf.date_document,
                    cf.statut, cf.montant_total
             FROM commande_fournisseur cf
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             WHERE cf.date_document = :d AND cf.deleted_at IS NULL AND cf.statut <> 'annulee'
             ORDER BY cf.oid_doc",
            [':d' => $date]);
        $totaux = $this->db->fetchOne(
            "SELECT COUNT(*) AS nb_commandes,
                    COALESCE(SUM(montant_total),0) AS total_ht
             FROM commande_fournisseur
             WHERE date_document = :d AND deleted_at IS NULL AND statut <> 'annulee'",
            [':d' => $date]);
        $this->journaliserImpression('etat_achats_jour', 0);
        $this->render('Approvisionnement/rapports/etat_achats_jour', compact('date','lignes','totaux'));
    }

    // ── Achats annuels ───────────────────────────────────────────────────────
    public function achatsAnnuels(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $annee   = (int)($_GET['annee'] ?? date('Y'));
        $parMois = $this->db->fetchAll(
            "SELECT EXTRACT(MONTH FROM date_document) AS mois,
                    COUNT(*) AS nb_commandes,
                    COALESCE(SUM(montant_total),0) AS total
             FROM commande_fournisseur
             WHERE EXTRACT(YEAR FROM date_document) = :a AND deleted_at IS NULL AND statut <> 'annulee'
             GROUP BY EXTRACT(MONTH FROM date_document)
             ORDER BY mois",
            [':a' => $annee]);
        $parFournisseur = $this->db->fetchAll(
            "SELECT f.nom AS fournisseur, COUNT(*) AS nb_commandes,
                    COALESCE(SUM(cf.montant_total),0) AS total
             FROM commande_fournisseur cf
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             WHERE EXTRACT(YEAR FROM cf.date_document) = :a AND cf.deleted_at IS NULL AND cf.statut <> 'annulee'
             GROUP BY f.nom ORDER BY total DESC",
            [':a' => $annee]);
        $totaux = $this->db->fetchOne(
            "SELECT COUNT(*) AS nb_commandes, COALESCE(SUM(montant_total),0) AS total
             FROM commande_fournisseur
             WHERE EXTRACT(YEAR FROM date_document) = :a AND deleted_at IS NULL AND statut <> 'annulee'",
            [':a' => $annee]);
        $this->journaliserImpression('achats_annuels', 0);
        $this->render('Approvisionnement/rapports/achats_annuels', compact('annee','parMois','parFournisseur','totaux'));
    }

    // ── Page de sélection des rapports ──────────────────────────────────────
    public function rapports(array $p = []): void
    {
        $this->requireRight('approvisionnement', 'imprimer');
        $this->renderInLayout('Approvisionnement/rapports/index', [], 'Rapports & Éditions');
    }

    // ── Utilitaires ───────────────────────────────────────────────────────────
    private function journaliserImpression(string $table, int $id): void
    {
        $this->db->execute(
            "INSERT INTO journal_audit(id_utilisateur, table_cible, action, id_enregistrement, ip_adresse)
             VALUES(:u, :t, 'IMPRESSION'::t_action_audit, :id, :ip::inet)",
            [':u'=>$_SESSION['user_id']??null, ':t'=>$table, ':id'=>$id, ':ip'=>$_SERVER['REMOTE_ADDR']??'0.0.0.0']
        );
    }
}
