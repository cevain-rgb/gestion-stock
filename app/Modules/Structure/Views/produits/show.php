<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('structure/produits') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-slate-800"><?= e($produit['designation']) ?></h1>
            <p class="text-sm font-mono text-violet-600"><?= e($produit['code']) ?></p>
        </div>
        <?php if($produit['en_alerte']): ?>
        <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1.5 rounded-full font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Alerte stock</span>
        <?php endif; ?>
        <?php if(!empty($_SESSION['droits']['structure.modifier'])): ?>
        <a href="<?= url('structure/produits/'.$produit['id_produit'].'/edit') ?>" class="btn-primary btn-sm"><i class="fa-solid fa-pen"></i> Modifier</a>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-4">Informations</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Famille</dt><dd><?= e($produit['famille_libelle']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Unité</dt><dd><?= e($produit['unite']) ?></dd></div>
                <?php if($produit['produit_pere_designation']): ?>
                <div class="flex justify-between"><dt class="text-slate-500">Produit père</dt><dd><?= e($produit['produit_pere_designation']) ?></dd></div>
                <?php endif; ?>
                <div class="flex justify-between"><dt class="text-slate-500">Fractionnaire</dt>
                <dd><?= $produit['is_fractionnaire']?'<span class="text-emerald-600">Oui — ×'.e($produit['facteur_fraction']).'</span>':'Non' ?></dd></div>
            </dl>
        </div>
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-4">Stock & Prix</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Stock actuel</dt>
                <dd class="font-bold text-lg <?= $produit['en_alerte']?'text-rose-600':'text-emerald-600' ?>">
                    <?= number_format((float)$produit['stock_actuel'],2,',',' ') ?> <?= e($produit['unite']) ?>
                </dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Seuil d'alerte</dt><dd><?= number_format((float)$produit['stock_alerte'],2,',',' ') ?> <?= e($produit['unite']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Valeur stock</dt><dd class="font-semibold text-violet-700"><?= money($produit['valeur_stock']) ?></dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-3"><dt class="text-slate-500">Prix achat</dt><dd><?= money($produit['prix_achat']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Prix vente</dt><dd class="font-semibold"><?= money($produit['prix_vente']) ?></dd></div>
            </dl>
        </div>
    </div>
    <?php if(!empty($fils)): ?>
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Produits fils (<?= count($fils) ?>)</h2></div>
        <table class="data-table"><thead><tr><th>Code</th><th>Désignation</th><th class="text-right">Stock</th><th class="text-right">Prix vente</th></tr></thead>
        <tbody>
        <?php foreach($fils as $f): ?>
        <tr>
            <td class="font-mono text-xs text-violet-600"><?= e($f['code']) ?></td>
            <td><?= e($f['designation']) ?></td>
            <td class="text-right"><?= number_format((float)$f['stock_actuel'],2,',',' ') ?> <?= e($f['unite']) ?></td>
            <td class="text-right font-semibold"><?= money($f['prix_vente']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
    <?php endif; ?>
</div>
