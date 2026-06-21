<?php
declare(strict_types=1);
namespace App\Modules\Structure\Controllers;

use App\Core\Controller;
use App\Core\Database;

class EditionStructureController extends Controller
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // ── Page index des éditions structure ────────────────────────────────────
    public function index(array $p = []): void
    {
        $this->requireRight('structure', 'imprimer');
        $familles = $this->db->fetchAll(
            "SELECT id_famille, libelle FROM famille_produit WHERE deleted_at IS NULL ORDER BY libelle");
        $this->renderInLayout('Structure/rapports/index', compact('familles'), 'Éditions — Structure');
    }

    // ── Liste des produits par famille (toutes familles) ─────────────────────
    public function listeProduitsFamilles(array $p = []): void
    {
        $this->requireRight('structure', 'imprimer');
        $familles = $this->db->fetchAll(
            "SELECT f.id_famille, f.libelle, f.description,
                    COUNT(p.id_produit) AS nb_produits,
                    COALESCE(SUM(p.stock_actuel * p.prix_achat),0) AS valeur_stock
             FROM famille_produit f
             LEFT JOIN produit p ON p.id_famille = f.id_famille AND p.deleted_at IS NULL
             WHERE f.deleted_at IS NULL
             GROUP BY f.id_famille, f.libelle, f.description
             ORDER BY f.libelle");
        $produits = $this->db->fetchAll(
            "SELECT p.id_famille, p.code, p.designation, p.unite,
                    p.prix_achat, p.prix_vente, p.stock_actuel, p.stock_alerte,
                    produit_est_en_alerte(p.*) AS en_alerte
             FROM produit p
             WHERE p.deleted_at IS NULL
             ORDER BY p.id_famille, p.designation");
        // Grouper par famille
        $produitsByFamille = [];
        foreach ($produits as $prod) {
            $produitsByFamille[$prod['id_famille']][] = $prod;
        }
        $this->journaliserImpression('produit', 0);
        $this->render('Structure/editions/liste_produits_familles',
            compact('familles','produitsByFamille'));
    }

    // ── Produits d'une famille donnée ─────────────────────────────────────────
    public function produitsFamille(array $p = []): void
    {
        $this->requireRight('structure', 'imprimer');
        $idFamille = (int)$p['id'];
        $famille   = $this->db->fetchOne(
            "SELECT * FROM famille_produit WHERE id_famille=:id AND deleted_at IS NULL", [':id'=>$idFamille]);
        if (!$famille) { $this->flash('error','Famille introuvable.'); $this->redirect('/structure/familles'); }

        $produits = $this->db->fetchAll(
            "SELECT p.code, p.designation, p.unite, p.prix_achat, p.prix_vente,
                    p.stock_actuel, p.stock_alerte,
                    produit_valeur_stock(p.*) AS valeur_stock,
                    produit_est_en_alerte(p.*) AS en_alerte,
                    pp.designation AS produit_pere
             FROM produit p
             LEFT JOIN produit pp ON pp.id_produit = p.id_produit_pere
             WHERE p.id_famille=:fam AND p.deleted_at IS NULL
             ORDER BY p.designation",
            [':fam'=>$idFamille]);
        $stats = $this->db->fetchOne(
            "SELECT COUNT(*) AS nb, COALESCE(SUM(produit_valeur_stock(p.*)),0) AS valeur_totale,
                    COUNT(*) FILTER (WHERE produit_est_en_alerte(p.*)) AS en_alerte
             FROM produit p WHERE p.id_famille=:fam AND p.deleted_at IS NULL",
            [':fam'=>$idFamille]);
        $this->journaliserImpression('famille_produit', $idFamille);
        $this->render('Structure/editions/produits_famille',
            compact('famille','produits','stats'));
    }

    // ── Liste des fournisseurs ────────────────────────────────────────────────
    public function listeFournisseurs(array $p = []): void
    {
        $this->requireRight('structure', 'imprimer');
        $fournisseurs = $this->db->fetchAll(
            "SELECT f.id_fournisseur, f.nom, f.created_at,
                    (f.contact).telephone AS telephone,
                    (f.contact).email AS email,
                    ((f.contact).adresse).ville AS ville,
                    ((f.contact).adresse).pays  AS pays,
                    COUNT(DISTINCT cf.oid_doc) AS nb_commandes,
                    COALESCE(SUM(cf.montant_total),0) AS total_commandes
             FROM fournisseur f
             LEFT JOIN commande_fournisseur cf ON cf.id_fournisseur=f.id_fournisseur
                 AND cf.deleted_at IS NULL AND cf.statut <> 'annulee'
             WHERE f.deleted_at IS NULL
             GROUP BY f.id_fournisseur, f.nom, f.created_at, f.contact
             ORDER BY f.nom");
        $this->journaliserImpression('fournisseur', 0);
        $this->render('Structure/editions/liste_fournisseurs', compact('fournisseurs'));
    }

    // ── Liste des clients ─────────────────────────────────────────────────────
    public function listeClients(array $p = []): void
    {
        $this->requireRight('structure', 'imprimer');
        $clients = $this->db->fetchAll(
            "SELECT c.id_client, c.nom,
                    (c.contact).telephone AS telephone,
                    (c.contact).email AS email,
                    ((c.contact).adresse).ville AS ville,
                    cc.libelle AS categorie, cc.remise_pct,
                    COUNT(DISTINCT cmd.oid_doc) AS nb_commandes,
                    COALESCE(SUM(cmd.montant_total),0) AS total_achats
             FROM client c
             JOIN categorie_client cc ON cc.id_categorie=c.id_categorie
             LEFT JOIN commande_client cmd ON cmd.id_client=c.id_client
                 AND cmd.deleted_at IS NULL AND cmd.statut <> 'annulee'
             WHERE c.deleted_at IS NULL
             GROUP BY c.id_client, c.nom, c.contact, cc.libelle, cc.remise_pct
             ORDER BY c.nom");
        $this->journaliserImpression('client', 0);
        $this->render('Structure/editions/liste_clients', compact('clients'));
    }

    // ── Liste des banques ─────────────────────────────────────────────────────
    public function listeBanques(array $p = []): void
    {
        $this->requireRight('structure', 'imprimer');
        $banques = $this->db->fetchAll(
            "SELECT b.id_banque, b.nom, b.numero_compte,
                    (b.adresse).ville AS ville, (b.adresse).pays AS pays,
                    COUNT(v.id_versement) AS nb_versements,
                    COALESCE(SUM(v.montant),0) AS total_verses
             FROM banque b
             LEFT JOIN versement_banque v ON v.id_banque=b.id_banque
             GROUP BY b.id_banque, b.nom, b.numero_compte, b.adresse
             ORDER BY b.nom");
        $this->journaliserImpression('banque', 0);
        $this->render('Structure/editions/liste_banques', compact('banques'));
    }

    // ── État de versements en banque par période ──────────────────────────────
    public function versementsBanque(array $p = []): void
    {
        $this->requireRight('structure', 'imprimer');
        $debut    = $_GET['debut']    ?? date('Y-m-01');
        $fin      = $_GET['fin']      ?? date('Y-m-d');
        $idBanque = (int)($_GET['id_banque'] ?? 0);

        $banques  = $this->db->fetchAll("SELECT id_banque, nom FROM banque ORDER BY nom");

        $where  = ["v.date_versement BETWEEN :debut AND :fin"];
        $params = [':debut'=>$debut, ':fin'=>$fin];
        if ($idBanque > 0) { $where[] = "v.id_banque=:b"; $params[':b']=$idBanque; }

        $versements = $this->db->fetchAll(
            "SELECT v.id_versement, v.date_versement, v.montant, v.reference,
                    b.nom AS banque
             FROM versement_banque v
             JOIN banque b ON b.id_banque=v.id_banque
             WHERE " . implode(' AND ', $where) . "
             ORDER BY v.date_versement DESC, b.nom",
            $params);
        $totaux = $this->db->fetchOne(
            "SELECT COUNT(*) AS nb, COALESCE(SUM(v.montant),0) AS total
             FROM versement_banque v WHERE " . implode(' AND ', $where),
            $params);

        $this->journaliserImpression('versement_banque', 0);
        $this->render('Structure/editions/versements_banque',
            compact('banques','versements','totaux','debut','fin','idBanque'));
    }

    private function journaliserImpression(string $table, int $id): void
    {
        $this->db->execute(
            "INSERT INTO journal_audit(id_utilisateur, table_cible, action, id_enregistrement, ip_adresse)
             VALUES(:u, :t, 'IMPRESSION'::t_action_audit, :id, :ip::inet)",
            [':u'=>$_SESSION['user_id']??null, ':t'=>$table, ':id'=>$id,
             ':ip'=>$_SERVER['REMOTE_ADDR']??'0.0.0.0']
        );
    }
}
