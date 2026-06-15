<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('audit') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800">Détail — entrée #<?= $entree['id_journal'] ?></h1>
    </div>

    <!-- Résumé -->
    <div class="card card-body mb-4">
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-xs text-slate-500 mb-1">Utilisateur</dt>
                <dd class="font-mono font-medium"><?= e($entree['user_login'] ?? 'système') ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 mb-1">Table cible</dt>
                <dd class="font-mono"><?= e($entree['table_cible']) ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 mb-1">Action</dt>
                <dd>
                    <?php
                    $colors = ['INSERT'=>'bg-emerald-100 text-emerald-700','UPDATE'=>'bg-blue-100 text-blue-700','DELETE'=>'bg-rose-100 text-rose-700','CONNEXION'=>'bg-violet-100 text-violet-700','DECONNEXION'=>'bg-slate-100 text-slate-500','IMPRESSION'=>'bg-amber-100 text-amber-700'];
                    $cls = $colors[$entree['action']] ?? 'bg-slate-100 text-slate-600';
                    ?>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $cls ?>"><?= e($entree['action']) ?></span>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 mb-1">ID enregistrement</dt>
                <dd class="font-mono"><?= $entree['id_enregistrement'] ?? '—' ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 mb-1">Adresse IP</dt>
                <dd class="font-mono text-slate-600"><?= e($entree['ip_adresse'] ?? '—') ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 mb-1">Date & heure</dt>
                <dd><?= dateFr($entree['created_at'], 'd/m/Y H:i:s') ?></dd>
            </div>
        </dl>
    </div>

    <!-- Diff anciennes / nouvelles valeurs -->
    <?php
    $old = $entree['anciennes_valeurs'] ? json_decode($entree['anciennes_valeurs'], true) : null;
    $new = $entree['nouvelles_valeurs'] ? json_decode($entree['nouvelles_valeurs'], true) : null;
    $allKeys = array_unique(array_merge(array_keys($old ?? []), array_keys($new ?? [])));
    ?>

    <?php if ($allKeys): ?>
    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="font-semibold text-slate-700">Comparaison des données</h2>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-100 inline-block"></span> Avant</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100 inline-block"></span> Après</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-2.5 text-left font-medium w-44">Champ</th>
                        <th class="px-4 py-2.5 text-left font-medium bg-rose-50/60">Ancienne valeur</th>
                        <th class="px-4 py-2.5 text-left font-medium bg-emerald-50/60">Nouvelle valeur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($allKeys as $key): ?>
                <?php
                $vOld = $old[$key] ?? null;
                $vNew = $new[$key] ?? null;
                $changed = $vOld !== $vNew;
                ?>
                <tr class="<?= $changed ? '' : 'opacity-50' ?>">
                    <td class="px-4 py-2.5 font-mono text-xs font-semibold text-slate-600"><?= e($key) ?></td>
                    <td class="px-4 py-2.5 bg-rose-50/30">
                        <?php if ($vOld !== null): ?>
                        <span class="font-mono text-xs <?= $changed ? 'bg-rose-100 text-rose-800 px-1.5 py-0.5 rounded' : 'text-slate-500' ?>">
                            <?= e(is_array($vOld) ? json_encode($vOld) : (string)$vOld) ?>
                        </span>
                        <?php else: ?><span class="text-slate-300 text-xs">—</span><?php endif; ?>
                    </td>
                    <td class="px-4 py-2.5 bg-emerald-50/30">
                        <?php if ($vNew !== null): ?>
                        <span class="font-mono text-xs <?= $changed ? 'bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded' : 'text-slate-500' ?>">
                            <?= e(is_array($vNew) ? json_encode($vNew) : (string)$vNew) ?>
                        </span>
                        <?php else: ?><span class="text-slate-300 text-xs">—</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card card-body text-center text-slate-400 py-8">
        <i class="fa-solid fa-circle-info text-2xl mb-2 opacity-40"></i>
        <p class="text-sm">Aucune donnée comparée (action événementielle).</p>
    </div>
    <?php endif; ?>
</div>
