<?php
// Page titre déjà défini dans le controller
$pageTitle = 'Tableau de bord';
?>

<!-- En-tête de page -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1>Tableau de bord</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            Bonjour, <?= e(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?> -
            <?= (new DateTime())->format('l d F Y') ?>
        </p>
    </div>
    <a href="<?= url('structure/produits') ?>"
        class="hidden sm:inline-flex items-center gap-2 text-sm bg-violet-600 hover:bg-violet-500 text-white px-4 py-2 rounded-lg transition font-medium">
        <i class="fa-solid fa-boxes-stacking"></i>
        Voir le stock
    </a>
</div>

<!-- ═══ KPI Cards ══════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

    <?php
    $cards = [
        ['Valeur du stock',   money($kpi['valeur_stock']),  'fa-solid fa-warehouse',          'bg-violet-50 text-violet-600',  'text-violet-700'],
        ['CA aujourd\'hui',   money($kpi['ca_jour']),       'fa-solid fa-chart-line',          'bg-emerald-50 text-emerald-600','text-emerald-700'],
        ['Achats du jour',    money($kpi['achats_jour']),   'fa-solid fa-truck-ramp-box',      'bg-blue-50 text-blue-600',      'text-blue-700'],
        ['Créances clients',  money($kpi['creances']),      'fa-solid fa-file-invoice-dollar', 'bg-amber-50 text-amber-600',    'text-amber-700'],
        ['Dettes fournisseurs',money($kpi['dettes']),       'fa-solid fa-industry',            'bg-orange-50 text-orange-600',  'text-orange-700'],
        ['Alertes stock',     $kpi['nb_alertes'] . ' produit(s)', 'fa-solid fa-triangle-exclamation', $kpi['nb_alertes'] > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400', $kpi['nb_alertes'] > 0 ? 'text-rose-700' : 'text-slate-600'],
    ];
    foreach ($cards as [$label, $val, $icon, $iconCls, $valCls]):
    ?>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center <?= $iconCls ?>">
                <i class="<?= $icon ?> text-sm"></i>
            </div>
        </div>
        <div class="text-lg font-bold <?= $valCls ?> leading-tight"><?= $val ?></div>
        <div class="text-xs text-slate-500 mt-0.5"><?= e($label) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ═══ Graphiques ═════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    <!-- Ventes vs Achats (2/3) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Ventes &amp; Achats</h2>
            <span class="text-xs text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">6 derniers mois</span>
        </div>
        <canvas id="chartVentesAchats" height="90"></canvas>
    </div>

    <!-- Stock par famille (1/3) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Stock par famille</h2>
        </div>
        <?php if (empty($stockFamilles)): ?>
            <div class="flex flex-col items-center justify-center h-40 text-slate-400 text-sm">
                <i class="fa-solid fa-boxes-stacking text-2xl mb-2 opacity-40"></i>
                Aucune donnée
            </div>
        <?php else: ?>
            <canvas id="chartFamilles" height="160"></canvas>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ Tableaux ════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <!-- Factures clients impayées -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Créances clients</h2>
            <a href="<?= url('vente/factures?statut=impayee') ?>"
                class="text-xs text-violet-600 hover:text-violet-800 font-medium transition">
                Voir tout <i class="fa-solid fa-arrow-right ml-0.5"></i>
            </a>
        </div>
        <?php if (empty($facturesImpayees)): ?>
            <div class="flex flex-col items-center justify-center py-10 text-slate-400 text-sm">
                <i class="fa-solid fa-circle-check text-2xl mb-2 text-emerald-400"></i>
                Aucune créance en cours
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                            <th class="px-4 py-2.5 text-left font-medium">Facture</th>
                            <th class="px-4 py-2.5 text-left font-medium">Client</th>
                            <th class="px-4 py-2.5 text-right font-medium">Reste</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($facturesImpayees as $f): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-mono text-xs text-violet-600"><?= e($f['numero_facture']) ?></td>
                            <td class="px-4 py-3 text-slate-700 truncate max-w-[120px]"><?= e($f['client']) ?></td>
                            <td class="px-4 py-3 text-right font-semibold text-rose-600">
                                <?= money($f['reste_a_payer']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Produits en alerte -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">
                Alertes stock
                <?php if ($kpi['nb_alertes'] > 0): ?>
                    <span class="ml-2 inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-bold">
                        <?= $kpi['nb_alertes'] > 9 ? '9+' : $kpi['nb_alertes'] ?>
                    </span>
                <?php endif; ?>
            </h2>
            <a href="<?= url('structure/produits?alerte=1') ?>"
                class="text-xs text-violet-600 hover:text-violet-800 font-medium transition">
                Voir tout <i class="fa-solid fa-arrow-right ml-0.5"></i>
            </a>
        </div>
        <?php if (empty($produitsAlerte)): ?>
            <div class="flex flex-col items-center justify-center py-10 text-slate-400 text-sm">
                <i class="fa-solid fa-circle-check text-2xl mb-2 text-emerald-400"></i>
                Tous les stocks sont suffisants
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                            <th class="px-4 py-2.5 text-left font-medium">Produit</th>
                            <th class="px-4 py-2.5 text-right font-medium">Stock</th>
                            <th class="px-4 py-2.5 text-right font-medium">Seuil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($produitsAlerte as $p): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800 truncate max-w-[150px]"><?= e($p['designation']) ?></div>
                                <div class="text-xs text-slate-400 font-mono"><?= e($p['code']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold text-rose-600"><?= number_format((float)$p['stock_actuel'],2,',',' ') ?></span>
                                <span class="text-xs text-slate-400 ml-0.5"><?= e($p['unite']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-right text-slate-400 text-xs">
                                <?= number_format((float)$p['stock_alerte'],2,',',' ') ?> <?= e($p['unite']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ Scripts Chart.js ═════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Palette commune
const VIOLET = '#7c3aed';
const EMERALD = '#10b981';
const SLATE   = '#94a3b8';

// Graphique Ventes / Achats
(function() {
    const ctx = document.getElementById('chartVentesAchats');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'Ventes',
                    data: <?= json_encode($chartVentes) ?>,
                    backgroundColor: 'rgba(124,58,237,0.15)',
                    borderColor: VIOLET,
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Achats',
                    data: <?= json_encode($chartAchats) ?>,
                    backgroundColor: 'rgba(16,185,129,0.15)',
                    borderColor: EMERALD,
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, color: '#64748b' } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + ctx.dataset.label + ' : ' + ctx.raw.toLocaleString('fr-FR') + ' FCFA'
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#94a3b8',
                        callback: v => (v >= 1000 ? (v/1000).toLocaleString('fr-FR') + 'k' : v) + ' F'
                    }
                }
            }
        }
    });
})();

// Graphique Donut familles
(function() {
    const ctx = document.getElementById('chartFamilles');
    if (!ctx) return;
    const familles = <?= json_encode(array_column($stockFamilles, 'libelle')) ?>;
    const valeurs  = <?= json_encode(array_map(fn($r) => round((float)$r['valeur']), $stockFamilles)) ?>;
    const colors   = ['#7c3aed','#10b981','#f59e0b','#3b82f6','#f43f5e','#06b6d4'];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: familles,
            datasets: [{
                data: valeurs,
                backgroundColor: colors.slice(0, familles.length),
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12, color: '#64748b', font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + ctx.raw.toLocaleString('fr-FR') + ' FCFA'
                    }
                }
            }
        }
    });
})();
</script>
