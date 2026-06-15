<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('archive') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-bold text-slate-800">Archive #<?= $archive['id_archive'] ?></h1>
            <p class="text-sm text-slate-500"><?= e($archive['entite']) ?> · ID <?= $archive['id_entite'] ?></p>
        </div>
        <?php if ($archive['action'] === 'suppression'): ?>
            <span class="ml-auto text-xs bg-rose-100 text-rose-700 px-2.5 py-1 rounded-full font-medium">
                <i class="fa-solid fa-trash mr-1"></i>Supprimé
            </span>
        <?php else: ?>
            <span class="ml-auto text-xs bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full font-medium">
                <i class="fa-solid fa-rotate-left mr-1"></i>Restauré
            </span>
        <?php endif; ?>
    </div>

    <!-- Métadonnées -->
    <div class="card card-body mb-4">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><dt class="text-xs text-slate-500 mb-1">Entité</dt><dd class="font-mono font-medium"><?= e($archive['entite']) ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">ID entité</dt><dd class="font-mono"><?= $archive['id_entite'] ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Archivé par</dt><dd class="font-mono"><?= e($archive['user_login'] ?? 'système') ?></dd></div>
            <div><dt class="text-xs text-slate-500 mb-1">Date</dt><dd><?= dateFr($archive['created_at'], 'd/m/Y H:i:s') ?></dd></div>
        </dl>
        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-100">
            <a href="<?= url('archive/'.$archive['id_archive'].'/xml') ?>" class="btn-secondary btn-sm">
                <i class="fa-solid fa-file-code"></i> Télécharger XML
            </a>
            <?php if ($archive['action'] === 'suppression'): ?>
            <form method="POST" action="<?= url('archive/'.$archive['id_archive'].'/restaurer') ?>">
                <?= csrfField() ?>
                <button class="btn-primary btn-sm">
                    <i class="fa-solid fa-rotate-left"></i> Restaurer
                </button>
            </form>
            <?php endif; ?>
            <?php if (!empty($_SESSION['droits']['securite.supprimer'])): ?>
            <form method="POST" action="<?= url('archive/'.$archive['id_archive'].'/supprimer') ?>"
                  onsubmit="return confirm('Supprimer définitivement cet élément ?')">
                <?= csrfField() ?>
                <button class="btn-danger btn-sm">
                    <i class="fa-solid fa-trash-can"></i> Supprimer définitivement
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Données extraites du XML -->
    <?php if (!empty($archive['champs'])): ?>
    <div class="card overflow-hidden mb-4">
        <div class="card-header">
            <h2 class="font-semibold text-slate-700">Données archivées</h2>
            <span class="text-xs text-slate-400"><?= count($archive['champs']) ?> champ(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-2.5 text-left font-medium w-48">Champ</th>
                        <th class="px-4 py-2.5 text-left font-medium">Valeur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($archive['champs'] as $nom => $valeur): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-mono text-xs font-semibold text-slate-600"><?= e($nom) ?></td>
                    <td class="px-4 py-2.5 text-sm <?= empty($valeur) ? 'text-slate-300 italic' : 'text-slate-700' ?>">
                        <?= $valeur !== '' ? e($valeur) : 'vide' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- XML brut -->
    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="font-semibold text-slate-700">XML brut</h2>
            <button onclick="copyXml()" class="btn-secondary btn-sm">
                <i class="fa-solid fa-copy"></i> Copier
            </button>
        </div>
        <div class="p-4">
            <pre id="xmlRaw" class="text-xs text-slate-600 bg-slate-50 rounded-lg p-4 overflow-x-auto whitespace-pre-wrap break-all border border-slate-100"><?= e($archive['xml_data']) ?></pre>
        </div>
    </div>
</div>

<script>
function copyXml() {
    const text = document.getElementById('xmlRaw').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.currentTarget;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copié !';
        setTimeout(() => btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copier', 2000);
    });
}
</script>
