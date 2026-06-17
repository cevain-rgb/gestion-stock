<?php
use App\Modules\Utilisateurs\Models\DroitModel;
$modules = DroitModel::MODULES;
$actions = DroitModel::ACTIONS;
$labelsModule = ['approvisionnement'=>'Approvisionnement','vente'=>'Vente','structure'=>'Structure','securite'=>'Sécurité'];
$labelsAction = ['consulter'=>'Consulter','creer'=>'Créer','modifier'=>'Modifier','supprimer'=>'Supprimer','imprimer'=>'Imprimer','regler'=>'Régler'];
?>
<div class="flex items-center gap-3 mb-6">
    <a href="<?= url('utilisateurs/groupes') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h1>Droits - <?= e($groupe['libelle']) ?></h1>
        <p class="text-sm text-slate-500">Définissez les permissions de ce groupe par module et action.</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="card-header">
        <span class="font-semibold text-slate-700">Matrice des droits</span>
        <div class="flex items-center gap-2 text-sm">
            <button type="button" onclick="cocheTout(true)" class="btn-secondary btn-sm">
                <i class="fa-solid fa-check-double"></i> Tout cocher
            </button>
            <button type="button" onclick="cocheTout(false)" class="btn-secondary btn-sm">
                <i class="fa-solid fa-xmark"></i> Tout décocher
            </button>
        </div>
    </div>

    <form method="POST" action="<?= url('utilisateurs/groupes/'.$groupe['id_groupe'].'/droits') ?>">
        <?= csrfField() ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-left font-medium w-44">Module</th>
                        <?php foreach ($actions as $a): ?>
                        <th class="px-3 py-3 text-center font-medium"><?= e($labelsAction[$a]) ?></th>
                        <?php endforeach; ?>
                        <th class="px-3 py-3 text-center font-medium text-violet-600">Tout</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($modules as $m): ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-5 py-4 font-semibold text-slate-700">
                        <i class="fa-solid fa-<?= $m==='approvisionnement'?'truck-ramp-box':($m==='vente'?'cart-shopping':($m==='structure'?'sitemap':'shield-halved')) ?> mr-2 text-violet-500 w-4"></i>
                        <?= e($labelsModule[$m]) ?>
                    </td>
                    <?php foreach ($actions as $a): ?>
                    <td class="px-3 py-4 text-center">
                        <input type="checkbox" name="<?= $m.'.'.$a ?>"
                            class="w-4 h-4 rounded accent-violet-600 cursor-pointer module-<?= $m ?>"
                            <?= !empty($matrice[$m][$a]) ? 'checked' : '' ?>>
                    </td>
                    <?php endforeach; ?>
                    <td class="px-3 py-4 text-center">
                        <input type="checkbox" class="w-4 h-4 rounded accent-emerald-500 cursor-pointer"
                            onchange="cocheModule('<?= $m ?>', this.checked)"
                            <?= array_sum($matrice[$m]) === count($actions) ? 'checked' : '' ?>>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100 bg-slate-50">
            <a href="<?= url('utilisateurs/groupes') ?>" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les droits
            </button>
        </div>
    </form>
</div>

<script>
function cocheTout(val) {
    document.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = val);
}
function cocheModule(mod, val) {
    document.querySelectorAll('.module-' + mod).forEach(cb => cb.checked = val);
}
</script>
