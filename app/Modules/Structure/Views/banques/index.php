<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Banques</h1>
    <p class="text-sm text-slate-500"><?= count($banques) ?> banque(s)</p></div>
    <div class="flex items-center gap-2">
        <?php if (!empty($_SESSION['droits']['structure.imprimer'])): ?>
        <a href="<?= url('structure/rapports/banques') ?>" target="_blank" class="btn-secondary btn-sm"><i class="fa-solid fa-print"></i> Imprimer liste</a>
        <a href="<?= url('structure/rapports/versements') ?>" target="_blank" class="btn-secondary btn-sm"><i class="fa-solid fa-money-bill-transfer"></i> État versements</a>
        <?php endif; ?>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="space-y-3">
    <?php foreach($banques as $b): ?>
    <div class="card p-5">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="font-bold text-slate-800"><?= e($b['nom']) ?></div>
                <?php if($b['numero_compte']): ?><div class="text-xs font-mono text-slate-500 mt-0.5"><?= e($b['numero_compte']) ?></div><?php endif; ?>
                <?php if($b['ville']): ?><div class="text-xs text-slate-400 mt-0.5"><i class="fa-solid fa-location-dot mr-1"></i><?= e($b['ville']) ?></div><?php endif; ?>
            </div>
            <div class="text-right">
                <div class="text-lg font-bold text-violet-700"><?= money($b['total_verses']) ?></div>
                <div class="text-xs text-slate-400">Total versé</div>
            </div>
        </div>
        <div class="flex items-center gap-1 border-t border-slate-100 pt-3">
            <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
            <button onclick="remplirEdit(<?= $b['id_banque'] ?>, '<?= e(addslashes($b['nom'])) ?>', '<?= e(addslashes($b['numero_compte']??'')) ?>', '<?= e(addslashes($b['ville']??'')) ?>')"
                class="btn-secondary btn-sm"><i class="fa-solid fa-pen"></i> Modifier</button>
            <?php endif; ?>
            <?php if(!empty($_SESSION['droits']['structure.creer'])): ?>
            <button onclick="remplirVersement(<?= $b['id_banque'] ?>)" class="btn-secondary btn-sm text-emerald-600">
                <i class="fa-solid fa-plus"></i> Versement
            </button>
            <?php endif; ?>
            <?php if(!empty($_SESSION['droits']['structure.supprimer'])): ?>
            <form method="POST" action="<?= url('structure/banques/'.$b['id_banque'].'/supprimer') ?>" onsubmit="return confirm('Supprimer cette banque ?')">
                <?= csrfField() ?><button class="btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($banques)): ?>
    <div class="card p-8 text-center text-slate-400"><i class="fa-solid fa-building-columns text-3xl mb-2 block opacity-30"></i>Aucune banque enregistrée.</div>
    <?php endif; ?>
    </div>

    <div class="space-y-4">
        <?php if(!empty($_SESSION['droits']['structure.creer'])): ?>
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-4">Nouvelle banque</h2>
            <form method="POST" action="<?= url('structure/banques/creer') ?>">
                <?= csrfField() ?>
                <div class="mb-3"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-input" required></div>
                <div class="mb-3"><label class="form-label">N° de compte</label><input type="text" name="numero_compte" class="form-input"></div>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div><label class="form-label text-xs">Ville</label><input type="text" name="ville" class="form-input py-2 text-sm"></div>
                    <div><label class="form-label text-xs">Pays</label><input type="text" name="pays" class="form-input py-2 text-sm"></div>
                </div>
                <button type="submit" class="btn-primary w-full"><i class="fa-solid fa-plus"></i> Créer</button>
            </form>
        </div>
        <div class="card card-body" id="versementCard" style="display:none">
            <h2 class="font-semibold text-slate-700 mb-4">Enregistrer un versement</h2>
            <form method="POST" id="versementForm">
                <?= csrfField() ?>
                <div class="mb-3"><label class="form-label">Montant (FCFA) *</label><input type="number" step="0.01" min="0.01" name="montant" class="form-input" required></div>
                <div class="mb-3"><label class="form-label">Date</label><input type="date" name="date_versement" class="form-input" value="<?= date('Y-m-d') ?>"></div>
                <div class="mb-4"><label class="form-label">Référence</label><input type="text" name="reference" class="form-input"></div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('versementCard').style.display='none'" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
        <div class="card card-body" id="editBanqueCard" style="display:none">
            <h2 class="font-semibold text-slate-700 mb-4">Modifier la banque</h2>
            <form method="POST" id="editBanqueForm">
                <?= csrfField() ?>
                <div class="mb-3"><label class="form-label">Nom *</label><input type="text" name="nom" id="editBNom" class="form-input" required></div>
                <div class="mb-3"><label class="form-label">N° de compte</label><input type="text" name="numero_compte" id="editBNC" class="form-input"></div>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div><label class="form-label text-xs">Ville</label><input type="text" name="ville" id="editBVille" class="form-input py-2 text-sm"></div>
                    <div><label class="form-label text-xs">Pays</label><input type="text" name="pays" class="form-input py-2 text-sm"></div>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('editBanqueCard').style.display='none'" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
function remplirEdit(id, nom, nc, ville) {
    const c = document.getElementById('editBanqueCard');
    c.style.display = 'block';
    document.getElementById('editBanqueForm').action = '<?= url('structure/banques/') ?>' + id + '/edit';
    document.getElementById('editBNom').value   = nom;
    document.getElementById('editBNC').value    = nc;
    document.getElementById('editBVille').value = ville;
    c.scrollIntoView({behavior:'smooth'});
}
function remplirVersement(id) {
    const c = document.getElementById('versementCard');
    c.style.display = 'block';
    document.getElementById('versementForm').action = '<?= url('structure/banques/') ?>' + id + '/versement';
    c.scrollIntoView({behavior:'smooth'});
}
</script>
