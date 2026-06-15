<?php
$labelsEntite = [
    'produit'            => ['Produit',          'fa-boxes-stacking'],
    'famille_produit'    => ['Famille produit',   'fa-sitemap'],
    'fournisseur'        => ['Fournisseur',        'fa-industry'],
    'client'             => ['Client',             'fa-user'],
    'utilisateur'        => ['Utilisateur',        'fa-users-cog'],
    'groupe_utilisateur' => ['Groupe',             'fa-shield-halved'],
    'commande_fournisseur'=> ['Cmd fournisseur',   'fa-file-lines'],
    'commande_client'    => ['Cmd client',         'fa-cart-shopping'],
    'bon_sortie'         => ['Bon de sortie',      'fa-box-open'],
    'don'                => ['Don',                'fa-gift'],
];
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Corbeille</h1>
        <p class="text-sm text-slate-500 mt-0.5"><?= $total ?> élément(s) archivé(s)</p>
    </div>
    <div class="flex items-center gap-2 text-xs text-slate-400 bg-amber-50 border border-amber-200 text-amber-700 px-3 py-2 rounded-lg">
        <i class="fa-solid fa-triangle-exclamation"></i>
        La suppression définitive est irréversible
    </div>
</div>

<!-- Stats par entité -->
<?php if (!empty($stats)): ?>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-5">
<?php foreach ($stats as $s):
    $info = $labelsEntite[$s['entite']] ?? [$s['entite'], 'fa-database'];
?>
<div class="card p-3 cursor-pointer hover:border-violet-300 transition"
     onclick="window.location='<?= url('archive?entite='.$s['entite']) ?>'">
    <div class="flex items-center gap-2 mb-1">
        <i class="fa-solid <?= $info[1] ?> text-slate-400 text-sm w-4"></i>
        <span class="text-xs text-slate-500 truncate"><?= e($info[0]) ?></span>
    </div>
    <div class="text-xl font-bold text-slate-800"><?= (int)$s['total'] ?></div>
    <div class="flex items-center gap-2 mt-1">
        <span class="text-[10px] text-rose-600"><i class="fa-solid fa-trash mr-0.5"></i><?= (int)$s['nb_suppression'] ?></span>
        <span class="text-[10px] text-emerald-600"><i class="fa-solid fa-rotate-left mr-0.5"></i><?= (int)$s['nb_restauration'] ?></span>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filtres -->
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('archive') ?>" class="flex flex-wrap items-end gap-3">
        <div class="w-44">
            <label class="form-label text-xs">Type d'entité</label>
            <select name="entite" class="form-select py-2 text-sm">
                <option value="">Toutes</option>
                <?php foreach ($entites as $ent): ?>
                <option value="<?= e($ent['entite']) ?>" <?= $filtres['entite'] === $ent['entite'] ? 'selected' : '' ?>>
                    <?= e($labelsEntite[$ent['entite']][0] ?? $ent['entite']) ?>
                    (<?= (int)$ent['nb'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-40">
            <label class="form-label text-xs">Action</label>
            <select name="action" class="form-select py-2 text-sm">
                <option value="">Toutes</option>
                <option value="suppression"  <?= $filtres['action'] === 'suppression'  ? 'selected' : '' ?>>Suppression</option>
                <option value="restauration" <?= $filtres['action'] === 'restauration' ? 'selected' : '' ?>>Restauration</option>
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
        <button type="submit" class="btn-primary py-2"><i class="fa-solid fa-filter"></i> Filtrer</button>
        <a href="<?= url('archive') ?>" class="btn-secondary py-2"><i class="fa-solid fa-xmark"></i></a>
    </form>
</div>

<!-- Tableau -->
<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr>
            <th>Entité</th>
            <th class="text-center">ID</th>
            <th>Aperçu XML</th>
            <th>Action</th>
            <th>Par</th>
            <th>Date</th>
            <th class="text-center">Actions</th>
        </tr></thead>
        <tbody>
        <?php if (empty($lignes)): ?>
            <tr><td colspan="7" class="text-center py-10 text-slate-400">
                <i class="fa-solid fa-trash-can text-2xl mb-2 block opacity-30"></i>
                La corbeille est vide.
            </td></tr>
        <?php endif; ?>
        <?php foreach ($lignes as $a): ?>
        <tr>
            <td>
                <?php $info = $labelsEntite[$a['entite']] ?? [$a['entite'], 'fa-database']; ?>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                        <i class="fa-solid <?= $info[1] ?> text-slate-500 text-xs"></i>
                    </div>
                    <span class="text-sm font-medium text-slate-700"><?= e($info[0]) ?></span>
                </div>
            </td>
            <td class="text-center font-mono text-xs text-slate-500"><?= $a['id_entite'] ?></td>
            <td class="max-w-xs">
                <code class="text-[10px] text-slate-400 truncate block max-w-[220px]" title="<?= e($a['xml_preview']) ?>">
                    <?= e(substr(strip_tags($a['xml_preview']), 0, 80)) ?>…
                </code>
            </td>
            <td>
                <?php if ($a['action'] === 'suppression'): ?>
                    <span class="inline-flex items-center gap-1 text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full font-medium">
                        <i class="fa-solid fa-trash text-[10px]"></i> Supprimé
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i> Restauré
                    </span>
                <?php endif; ?>
            </td>
            <td class="text-xs font-mono text-slate-500"><?= e($a['user_login'] ?? '—') ?></td>
            <td class="text-xs text-slate-400 whitespace-nowrap"><?= dateFr($a['created_at'], 'd/m/Y H:i') ?></td>
            <td>
                <div class="flex items-center justify-center gap-1">
                    <a href="<?= url('archive/'.$a['id_archive']) ?>" class="btn-secondary btn-sm" title="Détail">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="<?= url('archive/'.$a['id_archive'].'/xml') ?>" class="btn-secondary btn-sm" title="Télécharger XML">
                        <i class="fa-solid fa-file-code"></i>
                    </a>
                    <?php if ($a['action'] === 'suppression'): ?>
                    <form method="POST" action="<?= url('archive/'.$a['id_archive'].'/restaurer') ?>">
                        <?= csrfField() ?>
                        <button class="btn-secondary btn-sm text-emerald-600 hover:bg-emerald-50" title="Restaurer">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['droits']['securite.supprimer'])): ?>
                    <form method="POST" action="<?= url('archive/'.$a['id_archive'].'/supprimer') ?>"
                          onsubmit="return confirm('Supprimer définitivement ? Cette action est irréversible.')">
                        <?= csrfField() ?>
                        <button class="btn-danger btn-sm" title="Supprimer définitivement">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= paginationLinks($page, $totalPages, url('archive') . '?' . http_build_query(array_filter($filtres))) ?>
