<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Catégories clients</h1>
    <p class="text-sm text-slate-500"><?= count($categories) ?> catégorie(s)</p></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Liste -->
    <div class="card overflow-hidden">
        <table class="data-table"><thead><tr><th>Catégorie</th><th class="text-center">Remise</th><th class="text-center">Clients</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
        <?php foreach($categories as $c): ?>
        <tr>
            <td class="font-semibold text-slate-800"><?= e($c['libelle']) ?></td>
            <td class="text-center"><span class="text-emerald-700 font-semibold"><?= $c['remise_pct'] ?>%</span></td>
            <td class="text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 text-xs font-bold"><?= (int)$c['nb_clients'] ?></span></td>
            <td>
                <div class="flex items-center justify-center gap-1">
                <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
                <button onclick="remplirEdit(<?= $c['id_categorie'] ?>, '<?= e(addslashes($c['libelle'])) ?>', <?= $c['remise_pct'] ?>)"
                    class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></button>
                <?php endif; ?>
                <?php if(!empty($_SESSION['droits']['structure.supprimer'])): ?>
                <form method="POST" action="<?= url('structure/categories/'.$c['id_categorie'].'/supprimer') ?>" onsubmit="return confirm('Supprimer cette catégorie ?')">
                    <?= csrfField() ?><button class="btn-danger btn-sm" <?= (int)$c['nb_clients']>0?'disabled':'' ?>><i class="fa-solid fa-trash"></i></button>
                </form>
                <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
    <!-- Formulaire -->
    <div class="space-y-4">
        <?php if(!empty($_SESSION['droits']['structure.creer'])): ?>
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-4">Nouvelle catégorie</h2>
            <form method="POST" action="<?= url('structure/categories/creer') ?>">
                <?= csrfField() ?>
                <div class="mb-3"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-input" required></div>
                <div class="mb-4"><label class="form-label">Remise (%)</label><input type="number" step="0.01" min="0" max="100" name="remise_pct" class="form-input" value="0"></div>
                <button type="submit" class="btn-primary w-full"><i class="fa-solid fa-plus"></i> Créer</button>
            </form>
        </div>
        <?php endif; ?>
        <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
        <div class="card card-body" id="editCard" style="display:none">
            <h2 class="font-semibold text-slate-700 mb-4">Modifier la catégorie</h2>
            <form method="POST" id="editForm">
                <?= csrfField() ?>
                <div class="mb-3"><label class="form-label">Libellé *</label><input type="text" name="libelle" id="editLibelle" class="form-input" required></div>
                <div class="mb-4"><label class="form-label">Remise (%)</label><input type="number" step="0.01" min="0" max="100" name="remise_pct" id="editRemise" class="form-input"></div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('editCard').style.display='none'" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
function remplirEdit(id, libelle, remise) {
    document.getElementById('editCard').style.display = 'block';
    document.getElementById('editForm').action = '<?= url('structure/categories/') ?>' + id + '/edit';
    document.getElementById('editLibelle').value = libelle;
    document.getElementById('editRemise').value  = remise;
    document.getElementById('editCard').scrollIntoView({behavior:'smooth'});
}
</script>
