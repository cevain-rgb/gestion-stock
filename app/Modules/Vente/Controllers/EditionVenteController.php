<?php
declare(strict_types=1);
namespace App\Modules\Vente\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Modules\Vente\Models\{CommandeClientModel, LivraisonModel, FactureClientModel,
    ReglementClientModel, SortieModel};

class EditionVenteController extends Controller
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // ── Bon de commande client ────────────────────────────────────────────────
    public function bonCommande(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $m   = new CommandeClientModel();
        $cmd = $m->trouverParId((int)$p['id']);
        if (!$cmd) { $this->flash('error','Commande introuvable.'); $this->redirect('/vente/commandes'); }
        $cmd['lignes'] = $m->lignes((int)$p['id']);
        $this->journaliserImpression('commande_client', (int)$p['id']);
        $this->render('Vente/editions/bon_commande', compact('cmd'));
    }

    // ── Bon de livraison client ───────────────────────────────────────────────
    public function bonLivraison(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $m         = new LivraisonModel();
        $livraison = $m->trouverParId((int)$p['id']);
        if (!$livraison) { $this->flash('error','Livraison introuvable.'); $this->redirect('/vente/livraisons'); }
        $livraison['lignes'] = $m->lignes((int)$p['id']);
        // Récupérer les infos client via la commande
        $cmd = (new CommandeClientModel())->trouverParId((int)$livraison['id_commande_c']);
        $this->journaliserImpression('bon_livraison', (int)$p['id']);
        $this->render('Vente/editions/bon_livraison', compact('livraison', 'cmd'));
    }

    // ── Facture client ────────────────────────────────────────────────────────
    public function facture(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $m       = new FactureClientModel();
        $facture = $m->trouverParId((int)$p['id']);
        if (!$facture) { $this->flash('error','Facture introuvable.'); $this->redirect('/vente/factures'); }
        $facture['reglements'] = $m->reglements((int)$p['id']);
        // Récupérer les lignes de la commande liée pour le détail
        $lignes = (new CommandeClientModel())->lignes((int)$facture['id_commande_c']);
        $this->journaliserImpression('facture_client', (int)$p['id']);
        $this->render('Vente/editions/facture', compact('facture', 'lignes'));
    }

    // ── Reçu de règlement client ─────────────────────────────────────────────
    public function recu(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $regl    = $this->db->fetchOne("SELECT * FROM reglement_client WHERE id_reglement_c=:id", [':id'=>$p['id']]);
        if (!$regl) { $this->flash('error','Règlement introuvable.'); $this->redirect('/vente/factures'); }
        $facture = (new FactureClientModel())->trouverParId((int)$regl['id_facture_c']);
        $banque  = null;
        if (!empty($regl['id_banque'])) {
            $banque = $this->db->fetchOne("SELECT nom, numero_compte FROM banque WHERE id_banque=:id", [':id'=>$regl['id_banque']]);
        }
        $this->journaliserImpression('reglement_client', (int)$p['id']);
        $this->render('Vente/editions/recu', compact('regl','facture','banque'));
    }

    // ── Bon de sortie ─────────────────────────────────────────────────────────
    public function bonSortie(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $m      = new SortieModel();
        $sortie = $m->trouverParId((int)$p['id']);
        if (!$sortie) { $this->flash('error','Bon de sortie introuvable.'); $this->redirect('/vente/sorties'); }
        $sortie['lignes'] = $m->lignes((int)$p['id']);
        $this->journaliserImpression('bon_sortie', (int)$p['id']);
        $this->render('Vente/editions/bon_sortie', compact('sortie'));
    }

    // ── État des ventes par jour ──────────────────────────────────────────────
    public function etatVentesJour(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $date   = $_GET['date'] ?? date('Y-m-d');
        $lignes = $this->db->fetchAll(
            "SELECT cc.numero, c.nom AS client, cc.date_document,
                    cc.statut, cc.montant_total, cc.est_comptant
             FROM commande_client cc
             JOIN client c ON c.id_client = cc.id_client
             WHERE cc.date_document = :d AND cc.deleted_at IS NULL AND cc.statut <> 'annulee'
             ORDER BY cc.oid_doc",
            [':d' => $date]);
        $totaux = $this->db->fetchOne(
            "SELECT COUNT(*) AS nb_commandes,
                    COALESCE(SUM(montant_total),0)                               AS total,
                    COALESCE(SUM(montant_total) FILTER (WHERE est_comptant),0)   AS total_comptant,
                    COALESCE(SUM(montant_total) FILTER (WHERE NOT est_comptant),0) AS total_credit
             FROM commande_client
             WHERE date_document = :d AND deleted_at IS NULL AND statut <> 'annulee'",
            [':d' => $date]);
        $this->journaliserImpression('etat_ventes_jour', 0);
        $this->render('Vente/rapports/etat_ventes_jour', compact('date','lignes','totaux'));
    }

    // ── État des ventes annuelles ─────────────────────────────────────────────
    public function ventesAnnuelles(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $annee = (int)($_GET['annee'] ?? date('Y'));
        $parMois = $this->db->fetchAll(
            "SELECT EXTRACT(MONTH FROM date_document) AS mois,
                    COUNT(*) AS nb_commandes,
                    COALESCE(SUM(montant_total),0) AS total,
                    COALESCE(SUM(montant_total) FILTER (WHERE est_comptant),0)     AS total_comptant,
                    COALESCE(SUM(montant_total) FILTER (WHERE NOT est_comptant),0) AS total_credit
             FROM commande_client
             WHERE EXTRACT(YEAR FROM date_document) = :a AND deleted_at IS NULL AND statut <> 'annulee'
             GROUP BY EXTRACT(MONTH FROM date_document) ORDER BY mois",
            [':a' => $annee]);
        $parClient = $this->db->fetchAll(
            "SELECT c.nom AS client, COUNT(*) AS nb_commandes,
                    COALESCE(SUM(cc.montant_total),0) AS total
             FROM commande_client cc
             JOIN client c ON c.id_client = cc.id_client
             WHERE EXTRACT(YEAR FROM cc.date_document) = :a AND cc.deleted_at IS NULL AND cc.statut <> 'annulee'
             GROUP BY c.nom ORDER BY total DESC LIMIT 15",
            [':a' => $annee]);
        $totaux = $this->db->fetchOne(
            "SELECT COUNT(*) AS nb_commandes,
                    COALESCE(SUM(montant_total),0) AS total,
                    COALESCE(SUM(montant_total) FILTER (WHERE est_comptant),0)     AS total_comptant,
                    COALESCE(SUM(montant_total) FILTER (WHERE NOT est_comptant),0) AS total_credit
             FROM commande_client
             WHERE EXTRACT(YEAR FROM date_document) = :a AND deleted_at IS NULL AND statut <> 'annulee'",
            [':a' => $annee]);
        $this->journaliserImpression('ventes_annuelles', 0);
        $this->render('Vente/rapports/ventes_annuelles', compact('annee','parMois','parClient','totaux'));
    }

    // ── Page index rapports vente ────────────────────────────────────────────
    public function rapports(array $p = []): void
    {
        $this->requireRight('vente', 'imprimer');
        $this->renderInLayout('Vente/rapports/index', [], 'Rapports & Éditions — Vente');
    }

    private function journaliserImpression(string $table, int $id): void
    {
        $this->db->execute(
            "INSERT INTO journal_audit(id_utilisateur, table_cible, action, id_enregistrement, ip_adresse)
             VALUES(:u, :t, 'IMPRESSION'::t_action_audit, :id, :ip::inet)",
            [':u'=>$_SESSION['user_id']??null, ':t'=>$table, ':id'=>$id, ':ip'=>$_SERVER['REMOTE_ADDR']??'0.0.0.0']
        );
    }
}
