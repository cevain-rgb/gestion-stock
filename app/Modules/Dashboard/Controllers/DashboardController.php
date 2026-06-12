<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    /** GET /dashboard */
    public function index(array $params = []): void
    {
        $this->requireAuth();
        $db = Database::getInstance();

        // ── KPI ──────────────────────────────────────────────────────────────
        $kpi = [];

        // Valeur totale du stock
        $r = $db->fetchOne("SELECT COALESCE(SUM(stock_actuel * prix_achat),0) AS v FROM produit WHERE deleted_at IS NULL");
        $kpi['valeur_stock'] = (float)($r['v'] ?? 0);

        // Produits en alerte
        $r = $db->fetchOne("SELECT COUNT(*) AS v FROM produit WHERE stock_actuel <= stock_alerte AND deleted_at IS NULL");
        $kpi['nb_alertes'] = (int)($r['v'] ?? 0);

        // CA du jour
        $r = $db->fetchOne("SELECT COALESCE(SUM(total_montant_ht),0) AS v FROM v_ventes_par_jour WHERE jour = CURRENT_DATE");
        $kpi['ca_jour'] = (float)($r['v'] ?? 0);

        // Achats du jour
        $r = $db->fetchOne("SELECT COALESCE(SUM(total_montant_ht),0) AS v FROM v_achats_par_jour WHERE jour = CURRENT_DATE");
        $kpi['achats_jour'] = (float)($r['v'] ?? 0);

        // Créances clients (factures impayées)
        $r = $db->fetchOne("SELECT COALESCE(SUM(reste_a_payer),0) AS v FROM v_factures_c_impayees");
        $kpi['creances'] = (float)($r['v'] ?? 0);

        // Dettes fournisseurs
        $r = $db->fetchOne("SELECT COALESCE(SUM(reste_a_payer),0) AS v FROM v_factures_f_impayees");
        $kpi['dettes'] = (float)($r['v'] ?? 0);

        // ── Graphique : ventes & achats 6 derniers mois ───────────────────────
        $ventes = $db->fetchAll(
            "SELECT TO_CHAR(jour,'YYYY-MM') AS mois, SUM(total_montant_ht) AS total
             FROM v_ventes_par_jour
             WHERE jour >= DATE_TRUNC('month', NOW()) - INTERVAL '5 months'
             GROUP BY TO_CHAR(jour,'YYYY-MM')
             ORDER BY mois"
        );
        $achats = $db->fetchAll(
            "SELECT TO_CHAR(jour,'YYYY-MM') AS mois, SUM(total_montant_ht) AS total
             FROM v_achats_par_jour
             WHERE jour >= DATE_TRUNC('month', NOW()) - INTERVAL '5 months'
             GROUP BY TO_CHAR(jour,'YYYY-MM')
             ORDER BY mois"
        );

        // Construire les 6 derniers mois
        $moisLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $moisLabels[] = (new \DateTime("first day of -{$i} months"))->format('Y-m');
        }
        $ventesMap  = array_column($ventes, 'total', 'mois');
        $achatsMap  = array_column($achats, 'total', 'mois');
        $chartVentes = array_map(fn($m) => round((float)($ventesMap[$m] ?? 0)), $moisLabels);
        $chartAchats = array_map(fn($m) => round((float)($achatsMap[$m] ?? 0)), $moisLabels);
        $chartLabels = array_map(fn($m) => (new \DateTime($m . '-01'))->format('M Y'), $moisLabels);

        // ── Stock par famille ──────────────────────────────────────────────────
        $stockFamilles = $db->fetchAll(
            "SELECT f.libelle, COALESCE(SUM(p.stock_actuel * p.prix_achat),0) AS valeur
             FROM famille_produit f
             LEFT JOIN produit p ON p.id_famille = f.id_famille AND p.deleted_at IS NULL
             WHERE f.deleted_at IS NULL
             GROUP BY f.id_famille, f.libelle
             HAVING COALESCE(SUM(p.stock_actuel * p.prix_achat),0) > 0
             ORDER BY valeur DESC
             LIMIT 6"
        );

        // ── Dernières factures clients impayées ────────────────────────────────
        $facturesImpayees = $db->fetchAll(
            "SELECT * FROM v_factures_c_impayees ORDER BY date_facture DESC LIMIT 5"
        );

        // ── Produits en alerte ────────────────────────────────────────────────
        $produitsAlerte = $db->fetchAll(
            "SELECT code, designation, stock_actuel, stock_alerte, unite
             FROM produit WHERE stock_actuel <= stock_alerte AND deleted_at IS NULL
             ORDER BY stock_actuel ASC LIMIT 8"
        );

        $this->renderInLayout('Dashboard/index', compact(
            'kpi', 'chartLabels', 'chartVentes', 'chartAchats',
            'stockFamilles', 'facturesImpayees', 'produitsAlerte'
        ), 'Tableau de bord');
    }
}
