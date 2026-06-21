<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Éditions — Structure</h1>
        <p class="text-sm text-slate-500">Listes et états imprimables</p>
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <a href="<?= url('structure/rapports/produits-familles') ?>" target="_blank" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center mb-3"><i class="fa-solid fa-boxes-stacking text-violet-600"></i></div>
        <h3 class="font-semibold text-slate-800 mb-1">Produits par famille</h3>
        <p class="text-xs text-slate-500">Liste complète des produits regroupés par famille avec stocks et valeurs.</p>
    </a>
    <div class="card p-5 bg-slate-50">
        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mb-3"><i class="fa-solid fa-sitemap text-slate-400"></i></div>
        <h3 class="font-semibold text-slate-700 mb-2">Produits d'une famille</h3>
        <p class="text-xs text-slate-400 mb-3">Sélectionner depuis la liste des familles.</p>
        <select onchange="if(this.value)window.open('<?= url('structure/familles/') ?>'+this.value+'/produits','_blank')" class="form-select text-sm py-1.5">
            <option value="">— Choisir une famille —</option>
            <?php foreach($familles as $f): ?>
            <option value="<?= $f['id_famille'] ?>"><?= e($f['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <a href="<?= url('structure/rapports/fournisseurs') ?>" target="_blank" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3"><i class="fa-solid fa-industry text-blue-600"></i></div>
        <h3 class="font-semibold text-slate-800 mb-1">Liste des fournisseurs</h3>
        <p class="text-xs text-slate-500">Coordonnées, nombre de commandes et montants.</p>
    </a>
    <a href="<?= url('structure/rapports/clients') ?>" target="_blank" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-3"><i class="fa-solid fa-users text-emerald-600"></i></div>
        <h3 class="font-semibold text-slate-800 mb-1">Liste des clients</h3>
        <p class="text-xs text-slate-500">Coordonnées, catégorie, historique achats.</p>
    </a>
    <a href="<?= url('structure/rapports/banques') ?>" target="_blank" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center mb-3"><i class="fa-solid fa-building-columns text-amber-600"></i></div>
        <h3 class="font-semibold text-slate-800 mb-1">Liste des banques</h3>
        <p class="text-xs text-slate-500">Coordonnées, total des versements.</p>
    </a>
    <a href="<?= url('structure/rapports/versements') ?>" target="_blank" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center mb-3"><i class="fa-solid fa-money-bill-transfer text-rose-600"></i></div>
        <h3 class="font-semibold text-slate-800 mb-1">Versements en banque</h3>
        <p class="text-xs text-slate-500">État des versements par période et par banque.</p>
    </a>
</div>
