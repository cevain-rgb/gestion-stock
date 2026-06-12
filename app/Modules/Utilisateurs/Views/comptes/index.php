<?php
$perPage = $perPage ?? 20;
$totalPages = max(1, (int)ceil($total / $perPage));
?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Utilisateurs</h1>
        <p class="text-sm text-slate-500 mt-0.5"><?= $total ?> compte(s)</p>
    </div>
    <?php if (!empty($_SESSION['droits']['securite.creer'])): ?>
    <a href="<?= url('utilisateurs/comptes/creer') ?>" class="btn-primary">
        <i class="fa-solid fa-user-plus"></i> Nouvel utilisateur
    </a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<div class="card card-body mb-4">
    <form method="GET" action="<?= url('utilisateurs/comptes') ?>" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[180px]">
            <label class="form-label text-xs">Recherche</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="<?= e($filtres['recherche'] ?? '') ?>"
                    placeholder="Nom, prénom, login..." class="form-input pl-8 py-2 text-sm">
            </div>
        </div>
        <div class="w-40">
            <label class="form-label text-xs">Statut</label>
            <select name="actif" class="form-select py-2 text-sm">
                <option value="">Tous</option>
                <option value="1" <?= ($filtres['actif'] ?? '') === '1' ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= ($filtres['actif'] ?? '') === '0' ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>
        <div class="w-44">
            <label class="form-label text-xs">Groupe</label>
            <select name="groupe" class="form-select py-2 text-sm">
                <option value="">Tous les groupes</option>
                <?php foreach ($groupes as $g): ?>
                <option value="<?= $g['id_groupe'] ?>" <?= (string)($filtres['id_groupe'] ?? '') === (string)$g['id_groupe'] ? 'selected' : '' ?>>
                    <?= e($g['libelle']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-primary py-2">
            <i class="fa-solid fa-filter"></i> Filtrer
        </button>
        <a href="<?= url('utilisateurs/comptes') ?>" class="btn-secondary py-2">
            <i class="fa-solid fa-xmark"></i>
        </a>
    </form>
</div>

<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr>
            <th>Utilisateur</th>
            <th>Login</th>
            <th>Groupe</th>
            <th class="text-center">Statut</th>
            <th class="text-center">Depuis</th>
            <th class="text-center">Actions</th>
        </tr></thead>
        <tbody>
        <?php if (empty($lignes)): ?>
            <tr><td colspan="6" class="text-center py-10 text-slate-400">Aucun utilisateur trouvé.</td></tr>
        <?php endif; ?>
        <?php foreach ($lignes as $u): ?>
        <tr>
            <td>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-violet-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        <?= strtoupper(substr($u['prenom'] ?? 'U', 0, 1) . substr($u['nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-medium text-slate-800"><?= e($u['prenom'].' '.$u['nom']) ?></div>
                    </div>
                </div>
            </td>
            <td class="font-mono text-sm text-slate-600"><?= e($u['login']) ?></td>
            <td>
                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">
                    <?= e($u['groupe_libelle']) ?>
                </span>
            </td>
            <td class="text-center">
                <?php if ($u['actif']): ?>
                    <span class="inline-flex items-center gap-1 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                        <i class="fa-solid fa-circle text-[6px]"></i> Actif
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium">
                        <i class="fa-solid fa-circle text-[6px]"></i> Inactif
                    </span>
                <?php endif; ?>
            </td>
            <td class="text-center text-xs text-slate-400"><?= dateFr($u['created_at']) ?></td>
            <td>
                <div class="flex items-center justify-center gap-1">
                    <a href="<?= url('utilisateurs/comptes/'.$u['id_utilisateur']) ?>" class="btn-secondary btn-sm" title="Détail">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <?php if (!empty($_SESSION['droits']['securite.modifier'])): ?>
                    <a href="<?= url('utilisateurs/comptes/'.$u['id_utilisateur'].'/edit') ?>" class="btn-secondary btn-sm" title="Modifier">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <?php if ((int)$u['id_utilisateur'] !== (int)$_SESSION['user_id']): ?>
                    <form method="POST" action="<?= url('utilisateurs/comptes/'.$u['id_utilisateur'].'/actif') ?>">
                        <?= csrfField() ?>
                        <button class="btn-secondary btn-sm" title="<?= $u['actif'] ? 'Désactiver' : 'Activer' ?>">
                            <i class="fa-solid fa-<?= $u['actif'] ? 'toggle-off' : 'toggle-on' ?>"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['droits']['securite.supprimer']) && (int)$u['id_utilisateur'] !== (int)$_SESSION['user_id']): ?>
                    <form method="POST" action="<?= url('utilisateurs/comptes/'.$u['id_utilisateur'].'/supprimer') ?>"
                          onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        <?= csrfField() ?>
                        <button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= paginationLinks($page, $totalPages, url('utilisateurs/comptes')) ?>
