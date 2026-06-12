<?php $perPage = $perPage ?? 20; $totalPages = max(1, (int)ceil($total / $perPage)); ?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Groupes d'utilisateurs</h1>
        <p class="text-sm text-slate-500 mt-0.5"><?= $total ?> groupe(s)</p>
    </div>
    <?php if (!empty($_SESSION['droits']['securite.creer'])): ?>
    <a href="<?= url('utilisateurs/groupes/creer') ?>" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau groupe
    </a>
    <?php endif; ?>
</div>
<div class="card overflow-hidden">
    <table class="data-table">
        <thead><tr>
            <th>Groupe</th><th>Description</th><th class="text-center">Membres</th><th class="text-center">Actions</th>
        </tr></thead>
        <tbody>
        <?php if (empty($lignes)): ?>
            <tr><td colspan="4" class="text-center py-10 text-slate-400">Aucun groupe.</td></tr>
        <?php endif; ?>
        <?php foreach ($lignes as $g): ?>
        <tr>
            <td class="font-semibold text-slate-800"><?= e($g['libelle']) ?></td>
            <td class="text-slate-500 text-sm"><?= e($g['description'] ?: '-') ?></td>
            <td class="text-center">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-violet-100 text-violet-700 text-xs font-bold"><?= (int)$g['nb_utilisateurs'] ?></span>
            </td>
            <td>
                <div class="flex items-center justify-center gap-1">
                    <?php if (!empty($_SESSION['droits']['securite.modifier'])): ?>
                    <a href="<?= url('utilisateurs/groupes/'.$g['id_groupe'].'/droits') ?>" class="btn-secondary btn-sm" title="Droits"><i class="fa-solid fa-shield-halved"></i></a>
                    <a href="<?= url('utilisateurs/groupes/'.$g['id_groupe'].'/edit') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['droits']['securite.supprimer'])): ?>
                    <form method="POST" action="<?= url('utilisateurs/groupes/'.$g['id_groupe'].'/supprimer') ?>" onsubmit="return confirm('Supprimer ce groupe ?')">
                        <?= csrfField() ?><button class="btn-danger btn-sm" <?= (int)$g['nb_utilisateurs'] > 0 ? 'disabled' : '' ?>><i class="fa-solid fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= paginationLinks($page, $totalPages, url('utilisateurs/groupes')) ?>
