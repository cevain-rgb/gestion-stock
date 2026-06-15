<?php
$actions_audit = ['INSERT','UPDATE','DELETE','CONNEXION','DECONNEXION','IMPRESSION'];
$actionColors  = [
    'INSERT'      => 'bg-emerald-100 text-emerald-700',
    'UPDATE'      => 'bg-blue-100 text-blue-700',
    'DELETE'      => 'bg-rose-100 text-rose-700',
    'CONNEXION'   => 'bg-violet-100 text-violet-700',
    'DECONNEXION' => 'bg-slate-100 text-slate-500',
    'IMPRESSION'  => 'bg-amber-100 text-amber-700',
];
$actionIcons = [
    'INSERT'      => 'fa-plus',
    'UPDATE'      => 'fa-pen',
    'DELETE'      => 'fa-trash',
    'CONNEXION'   => 'fa-right-to-bracket',
    'DECONNEXION' => 'fa-right-from-bracket',
    'IMPRESSION'  => 'fa-print',
];
?>

<!-- En-tête -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Journal d'audit</h1>
        <p class="text-sm text-slate-500 mt-0.5"><?= number_format($total, 0, ',', ' ') ?> entrée(s) enregistrée(s)</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= url('audit/export?'.http_build_query($filtres)) ?>" class="btn-secondary">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </a>
        <?php if (!empty($_SESSION['droits']['securite.supprimer'])): ?>
        <button onclick="document.getElementById('modalPurge').classList.remove('hidden')" class="btn-danger">
            <i class="fa-solid fa-broom"></i> Purger
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- KPI Stats -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
<?php
$kpiCards = [
    ['Total',        $stats['total']         ?? 0, 'fa-list-check',        'text-slate-600',   'bg-slate-50'],
    ['Insertions',   $stats['nb_insert']     ?? 0, 'fa-plus-circle',       'text-emerald-600', 'bg-emerald-50'],
    ['Modifications',$stats['nb_update']     ?? 0, 'fa-pen-to-square',     'text-blue-600',    'bg-blue-50'],
    ['Suppressions', $stats['nb_delete']     ?? 0, 'fa-trash-can',         'text-rose-600',    'bg-rose-50'],
    ['Connexions',   $stats['nb_connexion']  ?? 0, 'fa-right-to-bracket',  'text-violet-600',  'bg-violet-50'],
    ['24 dernières h',$stats['derniere_24h'] ?? 0, 'fa-clock',             'text-amber-600',   'bg-amber-50'],
];
foreach ($kpiCards as [$lbl, $val, $ico, $txtCls, $bgCls]):
?>
<div class="card p-3">
    <div class="w-8 h-8 rounded-lg flex items-center justify-center <?= $bgCls ?> mb-2">
        <i class="fa-solid <?= $ico ?> text-sm <?= $txtCls ?>"></i>
    </div>
    <div class="text-lg font-bold text-slate-800"><?= number_format((int)$val, 0, ',', ' ') ?></div>
    <div class="text-xs text-slate-500"><?= $lbl ?></div>
</div>
<?php endforeach; ?>
</div>

<!-- Graphique activité 30j -->
<div class="card p-5 mb-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-slate-700">Activité — 30 derniers jours</h2>
        <span class="text-xs text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">Événements / jour</span>
    </div>
    <canvas id="chartActivite" height="55"></canvas>
</div>

<!-- Filtres -->
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('audit') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[160px]">
            <label class="form-label text-xs">Recherche</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="<?= e($filtres['recherche'] ?? '') ?>"
                    placeholder="Table, utilisateur, IP..." class="form-input pl-8 py-2 text-sm">
            </div>
        </div>
        <div class="w-40">
            <label class="form-label text-xs">Table</label>
            <select name="table" class="form-select py-2 text-sm">
                <option value="">Toutes</option>
                <?php foreach ($tables as $t): ?>
                <option value="<?= e($t['table_cible']) ?>" <?= $filtres['table'] === $t['table_cible'] ? 'selected' : '' ?>>
                    <?= e($t['table_cible']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-36">
            <label class="form-label text-xs">Action</label>
            <select name="action" class="form-select py-2 text-sm">
                <option value="">Toutes</option>
                <?php foreach ($actions_audit as $a): ?>
                <option value="<?= $a ?>" <?= $filtres['action'] === $a ? 'selected' : '' ?>><?= $a ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-40">
            <label class="form-label text-xs">Utilisateur</label>
            <select name="user_id" class="form-select py-2 text-sm">
                <option value="">Tous</option>
                <?php foreach ($utilisateurs as $u): ?>
                <option value="<?= $u['id_utilisateur'] ?>" <?= (string)$filtres['user_id'] === (string)$u['id_utilisateur'] ? 'selected' : '' ?>>
                    <?= e($u['login']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-36">
            <label class="form-label text-xs">Du</label>
            <input type="date" name="date_debut" value="<?= e($filtres['date_debut'] ?? '') ?>" class="form-input py-2 text-sm">
        </div>
        <div class="w-36">
            <label class="form-label text-xs">Au</label>
            <input type="date" name="date_fin" value="<?= e($filtres['date_fin'] ?? '') ?>" class="form-input py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary py-2">
            <i class="fa-solid fa-filter"></i> Filtrer
        </button>
        <a href="<?= url('audit') ?>" class="btn-secondary py-2" title="Réinitialiser">
            <i class="fa-solid fa-xmark"></i>
        </a>
    </form>
</div>

<!-- Tableau journal -->
<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr>
            <th class="w-8">#</th>
            <th>Utilisateur</th>
            <th>Table</th>
            <th>Action</th>
            <th class="text-center">Enreg.</th>
            <th>IP</th>
            <th>Date & heure</th>
            <th class="text-center">Détail</th>
        </tr></thead>
        <tbody>
        <?php if (empty($lignes)): ?>
            <tr><td colspan="8" class="text-center py-10 text-slate-400">Aucune entrée trouvée.</td></tr>
        <?php endif; ?>
        <?php foreach ($lignes as $e): ?>
        <tr>
            <td class="text-xs text-slate-400 font-mono"><?= $e['id_journal'] ?></td>
            <td>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-violet-700 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                        <?= strtoupper(substr($e['user_prenom'] ?? 'S', 0, 1) . substr($e['user_nom'] ?? 'Y', 0, 1)) ?>
                    </div>
                    <span class="text-sm font-mono text-slate-700"><?= e($e['user_login'] ?? 'système') ?></span>
                </div>
            </td>
            <td class="text-sm font-mono text-slate-600"><?= e($e['table_cible']) ?></td>
            <td>
                <?php $act = $e['action']; ?>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium <?= $actionColors[$act] ?? 'bg-slate-100 text-slate-600' ?>">
                    <i class="fa-solid <?= $actionIcons[$act] ?? 'fa-circle' ?> text-[10px]"></i>
                    <?= $act ?>
                </span>
            </td>
            <td class="text-center text-xs font-mono text-slate-500"><?= $e['id_enregistrement'] ?? '—' ?></td>
            <td class="text-xs font-mono text-slate-400"><?= e($e['ip_adresse'] ?? '—') ?></td>
            <td class="text-xs text-slate-500 whitespace-nowrap"><?= dateFr($e['created_at'], 'd/m/Y H:i:s') ?></td>
            <td class="text-center">
                <?php if ($e['anciennes_valeurs'] || $e['nouvelles_valeurs']): ?>
                <a href="<?= url('audit/'.$e['id_journal']) ?>" class="text-violet-500 hover:text-violet-700 transition" title="Voir le détail">
                    <i class="fa-solid fa-eye text-sm"></i>
                </a>
                <?php else: ?>
                <span class="text-slate-300"><i class="fa-solid fa-minus text-xs"></i></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= paginationLinks($page, $totalPages, url('audit') . '?' . http_build_query(array_filter($filtres))) ?>

<!-- Modal purge -->
<?php if (!empty($_SESSION['droits']['securite.supprimer'])): ?>
<div id="modalPurge" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center">
                <i class="fa-solid fa-broom text-rose-600"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg">Purger le journal</h3>
        </div>
        <p class="text-sm text-slate-500 mb-5">
            Cette action supprime définitivement les entrées du journal antérieures à la durée choisie.
            <strong class="text-rose-600">Irréversible.</strong>
        </p>
        <form method="POST" action="<?= url('audit/purger') ?>">
            <?= csrfField() ?>
            <div class="mb-5">
                <label class="form-label">Supprimer les entrées de plus de</label>
                <select name="jours" class="form-select">
                    <option value="30">30 jours</option>
                    <option value="60">60 jours</option>
                    <option value="90" selected>90 jours</option>
                    <option value="180">6 mois</option>
                    <option value="365">1 an</option>
                </select>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalPurge').classList.add('hidden')" class="btn-secondary">
                    Annuler
                </button>
                <button type="submit" class="btn-danger">
                    <i class="fa-solid fa-broom"></i> Confirmer la purge
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const ctx = document.getElementById('chartActivite');
    if (!ctx) return;
    const labels = <?= json_encode($graphe['labels']) ?>;
    const values = <?= json_encode($graphe['values']) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: values.map(v => v > 0 ? 'rgba(124,58,237,0.2)' : 'rgba(226,232,240,0.5)'),
                borderColor:     values.map(v => v > 0 ? '#7c3aed' : '#cbd5e1'),
                borderWidth: 1.5,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + c.raw + ' événement(s)' } } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', stepSize: 1 }, beginAtZero: true }
            }
        }
    });
})();
</script>
